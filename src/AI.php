<?php

namespace Backstage\AI;

use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\SystemMessage;

class AI
{
    public static function registerMacro(): void
    {
        Forms\Components\Field::macro('withAI', function ($prompt = null, $hint = true) {
            return $this->{$hint ? 'hintAction' : 'suffixAction'}(
                function (Set $set, Forms\Components\Field $component) use ($prompt) {
                    return Action::make('ai')
                        ->visible(fn ($operation) => $operation !== 'view')
                        ->icon(config('backstage.ai.action.icon'))
                        ->label(config('backstage.ai.action.label'))
                        ->modalHeading(config('backstage.ai.action.modal.heading'))
                        ->modalSubmitActionLabel('Generate')
                        ->form([
                            Forms\Components\Select::make('model')
                                ->label('Model')
                                ->options(
                                    collect(config('backstage.ai.providers'))
                                        ->mapWithKeys(fn ($provider, $model) => [
                                            $model => $model . ' (' . $provider->name . ')',
                                        ]),
                                )
                                ->default(key(config('backstage.ai.providers'))),

                            Forms\Components\Textarea::make('prompt')
                                ->label('Prompt')
                                ->autosize()
                                ->default($prompt),

                            Forms\Components\Section::make('configuration')
                                ->heading('Configuration')
                                ->schema([
                                    Forms\Components\TextInput::make('temperature')
                                        ->numeric()
                                        ->label('Temperature')
                                        ->default(config('backstage.ai.configuration.temperature'))
                                        ->helperText('The higher the temperature, the more creative the text')
                                        ->maxValue(1)
                                        ->minValue(0)
                                        ->step('0.1'),

                                    Forms\Components\TextInput::make('max_tokens')
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
                            $systemPrompts = AI::getSystemPrompts($data, $component);

                            try {
                                $response = Prism::text()
                                    ->using(config('backstage.ai.providers.' . $data['model']), $data['model'])
                                    ->withPrompt($data['prompt'])
                                    ->withSystemPrompts($systemPrompts)
                                    ->asText();

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

    public static function getSystemPrompts($data, Forms\Components\Field $component): array
    {
        $baseInstructions = [
            new SystemMessage('You are a helpful assistant. That\'s inside a Filament form field. This is the state of the field: ' . json_encode($component->getState())),
            new SystemMessage('You must only return the value of the field.'),
            new SystemMessage('No yapping, no explanations, no extra text.'),
        ];

        $instructions = [
            new SystemMessage('You must return a string value as output.'),
        ];

        if ($component instanceof Forms\Components\RichEditor) {
            $instructions = [
                new SystemMessage('You must return pure HTML as output.'),
                new SystemMessage('This is the field that will implement the HTML (state) that you will return: https://filamentphp.com/docs/3.x/forms/fields/rich-editor.'),
                new SystemMessage('Do not return any <h1> tags.'),
            ];
        }

        if ($component instanceof Forms\Components\MarkdownEditor) {
            $instructions = [
                new SystemMessage('You must return Markdown as output. This is the field that will implement the Markdown (state) that you will return: https://filamentphp.com/docs/3.x/forms/fields/markdown-editor.'),
                new SystemMessage("Don\'t return the markdown with markdown syntax like opening the markdown and closing it. For example: ```markdown... ```"),
            ];
        }

        if ($component instanceof Forms\Components\DateTimePicker) {
            $format = $component->getFormat();

            $instructions = [
                new SystemMessage('You must return a date as output.'),
                new SystemMessage('The date format is: ' . $format),
            ];
        }

        if ($component instanceof Forms\Components\TextInput && $component->isPassword()) {
            $instructions = [
                new SystemMessage('You must return a password as output.'),
            ];
        }

        if ($component instanceof Forms\Components\TextInput && $component->isEmail()) {
            $instructions = [
                new SystemMessage('You must return an email as output.'),
            ];
        }

        if ($component instanceof Forms\Components\Select) {
            $instructions = [
                new SystemMessage('You must return a value from the select as output.'),
                new SystemMessage('The options are: ' . json_encode($component->getOptions())),
                new SystemMessage('You must return the key of the option as output.'),
            ];
        }

        return array_merge($baseInstructions, $instructions);
    }
}
