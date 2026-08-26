<?php

namespace Backstage\AI;

use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Prism;

class AI
{
    public static function registerMacro(): void
    {
        Field::macro('withAI', function ($prompt = null) {
            if (is_callable($prompt)) {
                return $this->hintAction(
                    function (Set $set, Field $component) use ($prompt) {
                        return AI::createAIAction(function (Get $get, Set $set) use ($prompt, $component) {
                            $generatedPrompt = AI::callPrompt($prompt, $get, $set, $component);
                            $model = key(config('backstage.ai.providers'));

                            return AI::generateText($generatedPrompt, $model);
                        }, $component);
                    }
                );
            }

            return $this->hintAction(
                function (Set $set, Field $component) use ($prompt) {
                    $action = Action::make('ai')
                        ->icon(config('backstage.ai.action.icon'))
                        ->label(config('backstage.ai.action.label'))
                        ->modalHeading(config('backstage.ai.action.modal.heading'))
                        ->modalSubmitActionLabel('Generate')
                        ->form([
                            Select::make('model')
                                ->label('Model')
                                ->options(
                                    collect(config('backstage.ai.providers'))
                                        ->mapWithKeys(fn ($provider, $model) => [
                                            $model => $model . ' (' . $provider->name . ')',
                                        ]),
                                )
                                ->default(key(config('backstage.ai.providers'))),

                            Textarea::make('prompt')
                                ->label('Prompt')
                                ->autosize()
                                ->default($prompt),

                            Section::make('configuration')
                                ->heading('Configuration')
                                ->schema([
                                    TextInput::make('temperature')
                                        ->numeric()
                                        ->label('Temperature')
                                        ->default(config('backstage.ai.configuration.temperature'))
                                        ->helperText('The higher the temperature, the more creative the text')
                                        ->maxValue(1)
                                        ->minValue(0)
                                        ->step('0.1'),
                                    TextInput::make('max_tokens')
                                        ->numeric()
                                        ->label('Max tokens')
                                        ->default(config('backstage.ai.configuration.max_tokens'))
                                        ->helperText('The maximum number of tokens to generate')
                                        ->step('10')
                                        ->minValue(0)
                                        ->suffixAction(
                                            Action::make('increase')
                                                ->icon('heroicon-o-plus')
                                                ->action(fn (Set $set, Get $get) => $set('max_tokens', $get('max_tokens') + 100)),
                                        ),
                                ])
                                ->columns(2)
                                ->collapsed()
                                ->collapsible(),
                        ])
                        ->action(function ($data) use ($component, $set) {
                            AI::handleAIGeneration(function () use ($data) {
                                return AI::generateText($data['prompt'], $data['model']);
                            }, $component, $set);
                        });

                    return $action;
                }
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

    public static function handleAIGeneration(callable $generateCallback, Field $component, Set $set): void
    {
        try {
            $response = $generateCallback();
            $set($component->getName(), $response->text);
        } catch (PrismException $exception) {
            Notification::make()
                ->title('Text generation failed')
                ->body('Error: ' . $exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function generateText(string $prompt, string $model)
    {
        $providers = config('backstage.ai.providers', []);
        $provider = $providers[$model] ?? null;

        if ($provider === null) {
            throw new PrismException("AI provider not configured for model: {$model}. Please configure 'backstage.ai.providers.{$model}' in your config.");
        }

        $prism = Prism::text()
            ->using($provider, $model)
            ->withPrompt($prompt);

        if (str($model)->contains('gpt-5')) {
            $prism->withProviderOptions([
                'reasoning' => ['effort' => 'low'],
            ]);
        }

        return $prism->asText();
    }
}
