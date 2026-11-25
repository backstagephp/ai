<?php

namespace Backstage\AI;

use Backstage\AI\Enums\ContentFormat;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Prism;

class AI
{
    public static function registerMacro(): void
    {
        Field::macro('withAI', function (string | callable | null $prompt = null, string | Closure | null $context = null) {
            // A string is always the modal's default instruction, also when it
            // happens to name a PHP function: `withAI('Copy')` must not call copy().
            if ($prompt instanceof Closure || (is_callable($prompt) && ! is_string($prompt))) {
                return $this->hintAction(
                    function (Field $component) use ($prompt, $context) {
                        return AI::createAIAction(function (Get $get, Set $set) use ($prompt, $context, $component) {
                            return AI::generateForField(
                                $component,
                                AI::callPrompt($prompt, $get, $set, $component),
                                context: $context,
                            );
                        }, $component);
                    }
                );
            }

            return $this->hintAction(
                fn (Field $component) => AI::createAIModalAction($component, $prompt, $context)
            );
        });
    }

    /**
     * The utilities go first because that is what a prompt actually reads, and
     * PHP lets a closure declare only the arguments it wants, so
     * `function (Get $get)` stays valid while a closure that needs the field
     * can take all three. Flipping this order is what broke the SEO buttons:
     * the field arrived where the closure expected a Get.
     */
    public static function callPrompt(callable $prompt, Get $get, Set $set, Field $component): string
    {
        return $prompt($get, $set, $component);
    }

    public static function providerName(Provider | string $provider): string
    {
        if ($provider instanceof Provider) {
            return $provider->name;
        }

        return Provider::tryFrom($provider)?->name ?? $provider;
    }

    public static function defaultModel(): string
    {
        return (string) array_key_first(config('backstage.ai.providers', []));
    }

    public static function createAIAction(callable $generateCallback, Field $component): Action
    {
        return Action::make('ai')
            ->icon(config('backstage.ai.action.icon'))
            ->label(config('backstage.ai.action.label'))
            ->action(function (Get $get, Set $set) use ($generateCallback, $component) {
                AI::handleAIGeneration(function () use ($generateCallback, $get, $set) {
                    return $generateCallback($get, $set);
                }, $component, $set);
            });
    }

    /**
     * The modal behind a bare `withAI()`: the user types an instruction and the
     * field's current text is sent along with it, so "rewrite this" has
     * something to rewrite.
     */
    public static function createAIModalAction(Field $component, ?string $defaultPrompt = null, string | Closure | null $context = null): Action
    {
        return Action::make('ai')
            ->icon(config('backstage.ai.action.icon'))
            ->label(config('backstage.ai.action.label'))
            ->modalHeading(__((string) config('backstage.ai.action.modal.heading')))
            ->modalDescription(fn (): string => FieldContent::isBlank(FieldContent::read($component))
                ? __('Describe what should be written in this field.')
                : __('The current text of this field is sent to the AI provider and replaced by the result.'))
            ->modalSubmitActionLabel(__('Generate'))
            ->schema([
                Select::make('model')
                    ->label(__('Model'))
                    ->options(
                        collect(config('backstage.ai.providers'))
                            ->mapWithKeys(fn ($provider, $model) => [
                                $model => $model . ' (' . AI::providerName($provider) . ')',
                            ]),
                    )
                    ->default(AI::defaultModel())
                    ->required(),

                Textarea::make('prompt')
                    ->label(__('Instruction'))
                    ->helperText(__('Say what should happen with the text, for example "Rewrite this more formally" or "Summarise in three sentences".'))
                    ->autosize()
                    ->default($defaultPrompt)
                    ->required(),

                Section::make('configuration')
                    ->heading(__('Configuration'))
                    ->schema([
                        TextInput::make('temperature')
                            ->numeric()
                            ->label(__('Temperature'))
                            ->default(config('backstage.ai.configuration.temperature'))
                            ->helperText(__('The higher the temperature, the more creative the text. Ignored by models that do not support it.'))
                            ->maxValue(1)
                            ->minValue(0)
                            ->step('0.01'),
                        TextInput::make('max_tokens')
                            ->numeric()
                            ->label(__('Max tokens'))
                            ->default(config('backstage.ai.configuration.max_tokens'))
                            ->helperText(__('The maximum number of tokens to generate. A text that does not fit is not written.'))
                            ->step('1')
                            ->minValue(1)
                            ->suffixAction(
                                Action::make('increase')
                                    ->icon('heroicon-o-plus')
                                    ->action(fn (Set $set, Get $get) => $set('max_tokens', (int) $get('max_tokens') + 100)),
                            ),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->collapsible(),
            ])
            ->action(function (array $data, Set $set) use ($component, $context) {
                AI::handleAIGeneration(function () use ($data, $component, $context) {
                    return AI::generateForField(
                        $component,
                        FieldPrompt::user($data['prompt'], FieldContent::read($component)),
                        $data['model'],
                        $context,
                        filled($data['temperature'] ?? null) ? (float) $data['temperature'] : null,
                        filled($data['max_tokens'] ?? null) ? (int) $data['max_tokens'] : null,
                    );
                }, $component, $set);
            });
    }

    /**
     * Writes a generated answer into the field, unless there is nothing that
     * should be written: a failed request, an answer that was cut off, or the
     * model saying it had nothing to work with. In those cases the field keeps
     * what it had and the user is told why.
     */
    public static function handleAIGeneration(callable $generateCallback, Field $component, Set $set): void
    {
        try {
            $response = $generateCallback();
        } catch (PrismException $exception) {
            Notification::make()
                ->title(__('Text generation failed'))
                ->body(__('Error: :message', ['message' => $exception->getMessage()]))
                ->danger()
                ->send();

            return;
        }

        if (($response->finishReason ?? null) === FinishReason::Length) {
            Notification::make()
                ->title(__('The text was cut off'))
                ->body(__('The answer did not fit within the maximum number of tokens, so the field was left unchanged. Raise "Max tokens" and try again.'))
                ->warning()
                ->send();

            return;
        }

        $content = FieldContent::clean($response->text, ContentFormat::fromField($component));

        if ($content === null) {
            Notification::make()
                ->title(__('Nothing to write'))
                ->body(__('The AI had nothing to work with, so the field was left unchanged. Put text in the field first, or describe in the instruction what should be written.'))
                ->warning()
                ->send();

            return;
        }

        $set($component->getName(), $content);
    }

    /**
     * Generates content for one field: the model is told it is filling that
     * field, in the format the field stores, and what the field is for.
     */
    public static function generateForField(
        Field $component,
        string $prompt,
        ?string $model = null,
        string | Closure | null $context = null,
        ?float $temperature = null,
        ?int $maxTokens = null,
    ) {
        $maxLength = method_exists($component, 'getMaxLength') ? $component->getMaxLength() : null;

        return static::generateText(
            $prompt,
            $model ?? static::defaultModel(),
            FieldPrompt::system(
                ContentFormat::fromField($component),
                $component->evaluate($context),
                is_numeric($maxLength) ? (int) $maxLength : null,
            ),
            $temperature,
            $maxTokens,
        );
    }

    /**
     * The bare request. Called with only a prompt and a model it sends exactly
     * that, with no system prompt and no limits, so callers that want raw output
     * (a JSON list, for instance) keep getting it.
     */
    public static function generateText(
        string $prompt,
        string $model,
        ?string $systemPrompt = null,
        ?float $temperature = null,
        ?int $maxTokens = null,
    ) {
        $providers = config('backstage.ai.providers', []);
        $provider = $providers[$model] ?? null;

        if ($provider === null) {
            throw new PrismException("AI provider not configured for model: {$model}. Please configure 'backstage.ai.providers.{$model}' in your config.");
        }

        $prism = Prism::text()
            ->using($provider, $model)
            ->withPrompt($prompt);

        if (filled($systemPrompt)) {
            $prism->withSystemPrompt($systemPrompt);
        }

        if ($temperature !== null && static::supportsTemperature($model)) {
            $prism->usingTemperature($temperature);
        }

        if ($maxTokens !== null) {
            $prism->withMaxTokens($maxTokens);
        }

        if (str($model)->contains('gpt-5')) {
            $prism->withProviderOptions([
                'reasoning' => ['effort' => 'low'],
            ]);
        }

        return $prism->asText();
    }

    /**
     * Reasoning models reject a temperature outright, so it is left out for
     * them rather than failing the request.
     */
    public static function supportsTemperature(string $model): bool
    {
        return ! str($model)->lower()->startsWith(['gpt-5', 'o1', 'o3', 'o4']);
    }
}
