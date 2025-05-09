<?php

namespace Backstage\AI;

use Backstage\AI\Prism\SystemMessages\Forms\Components\BaseInstructions;
use Backstage\AI\Prism\SystemMessages\Forms\Components\DateTimePicker;
use Backstage\AI\Prism\SystemMessages\Forms\Components\MarkdownEditor;
use Backstage\AI\Prism\SystemMessages\Forms\Components\RichEditor;
use Backstage\AI\Prism\SystemMessages\Forms\Components\Select;
use Backstage\AI\Prism\SystemMessages\Forms\Components\TextInput;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;

class AI
{
    public static function registerFormMacro(): void
    {
        Forms\Components\Field::macro('withAI', function ($prompt = null, $hint = true) {
            return $this->{$hint ? 'hintAction' : 'suffixAction'}(
                function (Set $set, Forms\Components\Field $component) use ($prompt) {
                    return Action::make('ai')
                        ->visible(fn ($operation) => $operation !== 'view')
                        ->icon(config('backstage.ai.action.icon'))
                        ->label(config('backstage.ai.action.label'))
                        ->modalHeading(config('backstage.ai.action.modal.heading'))
                        ->modalSubmitActionLabel(__('Generate'))
                        ->form([
                            Forms\Components\Select::make('model')
                                ->label(__('AI Model'))
                                ->options(
                                    collect(config('backstage.ai.providers'))
                                        ->mapWithKeys(fn ($provider, $model) => [
                                            $model => $model . ' (' . $provider->name . ')',
                                        ]),
                                )
                                ->default(key(config('backstage.ai.providers'))),

                            Forms\Components\Textarea::make('prompt')
                                ->label(__('Instructions'))
                                ->autosize()
                                ->default($prompt),

                            Forms\Components\Section::make('configuration')
                                ->heading(__('Configuration'))
                                ->schema([
                                    Forms\Components\TextInput::make('temperature')
                                        ->numeric()
                                        ->label(__('AI Temperature'))
                                        ->default(config('backstage.ai.configuration.temperature'))
                                        ->helperText('The higher the temperature, the more creative the text')
                                        ->maxValue(1)
                                        ->minValue(0)
                                        ->step('0.1'),

                                    Forms\Components\TextInput::make('max_tokens')
                                        ->numeric()
                                        ->label(__('Max Tokens'))
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
                            $systemPrompts = AI::getSystemPrompts($component);

                            try {
                                $response = Prism::text()
                                    ->using(config('backstage.ai.providers.' . $data['model']), $data['model'])
                                    ->withPrompt($data['prompt'])
                                    ->withSystemPrompts($systemPrompts)
                                    ->asText();

                                $fieldState = $component->getState();

                                if ($fieldState === $response->text) {
                                    Notification::make()
                                        ->title(__('AI generated text is the same as the current state'))
                                        ->body(__('Please be more specific with your prompt or try again.'))
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $set($component->getName(), $response->text);
                            } catch (PrismException $exception) {
                                Notification::make()
                                    ->title('Text generation failed')
                                    ->body('Error: ' . $exception->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        });
                }
            );
        });
    }

    /**
     * Checking the type of the component and returning the specific instructions for each type.
     * Allowed types are:
     *
     * @var Forms\Components\RichEditor
     * @var Forms\Components\MarkdownEditor
     * @var Forms\Components\DateTimePicker
     * @var Forms\Components\TextInput
     * @var Forms\Components\Select
     */
    public static function getSystemPrompts(Forms\Components\Field $component): array
    {
        $baseInstructions = BaseInstructions::ask($component);

        $componentInstructions = match (true) {
            $component instanceof Forms\Components\RichEditor => RichEditor::ask($component),
            $component instanceof Forms\Components\MarkdownEditor => MarkdownEditor::ask($component),
            $component instanceof Forms\Components\DateTimePicker => DateTimePicker::ask($component),
            $component instanceof Forms\Components\TextInput => TextInput::ask($component),
            $component instanceof Forms\Components\Select => Select::ask($component),
            default => [],
        };

        return array_merge($baseInstructions, $componentInstructions);
    }
}
