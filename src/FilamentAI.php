<?php

namespace Vormkracht10\FilamentAI;

use Filament\Forms\Set;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;

class FilamentAI
{
    public static function registerMacro(): void
    {
        Field::macro('withAI', function ($prompt = null) {
            return $this->hintAction(
                function (Set $set, Field $component) use ($prompt) {
                    return Action::make('ai')
                        ->icon(config('filament-ai.icon'))
                        ->label(config('filament-ai.label'))
                        ->form([
                            Section::make([
                                TextInput::make('max_tokens')
                                    ->label('Max tokens')
                                    ->default(300)
                                    ->numeric(),

                                TextInput::make('temperature')
                                    ->numeric()
                                    ->label('Temperature')
                                    ->default(0.7)
                                    ->maxValue(1)
                                    ->minValue(0)
                                    ->step('0.1'),
                            ])->columns(2),

                            Textarea::make('prompt')
                                ->label('Prompt')
                                ->default($prompt),
                        ])
                        ->modalSubmitActionLabel('Generate')
                        ->action(function ($data) use ($component, $set) {
                            try {
                                // $result = OpenAI::completions()->create([
                                //     'model' => 'text-davinci-003',
                                //     'prompt' => $data['prompt'],
                                //     'max_tokens' => (int)$data['max_tokens'],
                                //     'temperature' => (float)$data['temperature'],
                                // ]);

                                $generatedText = 'Bla bla bla';

                                $set($component->getName(), $generatedText);
                            } catch (\Throwable $exception) {
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
