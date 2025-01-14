<?php

namespace Vormkracht10\FilamentAI;

use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Exceptions\PrismException;
use EchoLabs\Prism\Prism;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

class FilamentAI
{
    public static function registerMacro(): void
    {
        Field::macro('withAI', function ($prompt = null) {
            return $this->hintAction(
                function (Set $set, Field $component) use ($prompt) {
                    return Action::make('ai')
                        ->icon(config('filament-ai.action.icon'))
                        ->label(config('filament-ai.action.label'))
                        ->modalHeading(config('filament-ai.action.modal.heading'))
                        ->modalSubmitActionLabel('Generate')
                        ->form([
                            Textarea::make('prompt')
                                ->label('Prompt')
                                ->autosize()
                                ->default($prompt),
                        ])
                        ->action(function ($data) use ($component, $set) {
                            try {
                                $response = Prism::text()
                                    ->using(Provider::OpenAI, 'gpt-4o-mini')
                                    ->withPrompt($data['prompt'])
                                    ->generate();

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
