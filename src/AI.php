<?php

namespace Backstage\AI;

use Backstage\AI\Models\Prompt;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;

class AI
{
    public static function registerMacro(): void
    {
        Field::macro('withAI', function ($prompt = null) {
            return $this->hintAction(
                function (Set $set, Field $component) use ($prompt) {
                    return Action::make('ai')
                        ->icon(config('backstage.ai.action.icon'))
                        ->label(config('backstage.ai.action.label'))
                        ->modalHeading(config('backstage.ai.action.modal.heading'))
                        ->modalSubmitActionLabel('Generate')
                        ->form([
                            Select::make('prompt_id')
                                ->label(__('Prompt'))
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function (Set $set, int $state) {
                                    $designatedPrompt = Prompt::find($state);

                                    if (!$designatedPrompt) {
                                        return;
                                    }

                                    $set('model', $designatedPrompt->model);
                                    $set('temperature', $designatedPrompt->temperature);
                                    $set('max_tokens', $designatedPrompt->max_tokens);
                                })
                                ->options(fn() => Prompt::all()->pluck('name', 'id')),

                            Select::make('model')
                                ->label('Model')
                                ->native(false)
                                ->visible(fn(Get $get) => $get('prompt_id') !== null)
                                ->live()
                                ->options(fn() => collect(config('backstage.ai.providers'))->mapWithKeys(fn($item, $key) => [ucfirst($key) => $item])),

                            Textarea::make('prompt')
                                ->label('Prompt')
                                ->autosize()
                                ->visible(fn(Get $get) => $get('prompt_id') !== null)
                                ->live()
                                ->default($prompt),

                            Section::make('configuration')
                                ->heading('Configuration')
                                ->visible(fn(Get $get) => $get('prompt_id') !== null)
                                ->live()
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
                                                ->action(fn(Set $set, Get $get) => $set('max_tokens', $get('max_tokens') + 100)),
                                        ),
                                ])
                                ->columns(2)
                                ->collapsed()
                                ->collapsible(),
                        ])
                        ->action(function ($data) use ($component, $set) {
                            try {
                                $providerKey = collect(config('backstage.ai.providers'))
                                    ->filter(fn($models) => array_key_exists($data['model'], $models))
                                    ->keys()
                                    ->first();

                                $provider = Provider::from($providerKey);

                                $prompt = Prompt::find($data['prompt_id']);

                                $response = Prism::text()
                                    ->using($provider, $data['model'])
                                    ->withPrompt($data['prompt'])
                                    ->withSystemPrompt($prompt->prompt)
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
}
