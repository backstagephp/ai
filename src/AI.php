<?php

namespace Backstage\AI;

use Backstage\AI\Events\CaptureAiRequest;
use Backstage\AI\Facades\AI as FacadesAI;
use Prism\Prism\Enums\Provider as ProviderEnum;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\Text\Response;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

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

                            Checkbox::make('use_existing')
                                ->label(__('Use existing text'))
                                ->default(false)
                                ->helperText(__('If checked, the existing text will be used as context for the AI.')),

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
                            $AIquery = str('')
                                ->prepend("Respond only with the essential answer—no extra commentary.\n");

                            if ($data['use_existing']) {
                                $AIquery = $AIquery->append("If I mention the existing text, use it; otherwise, ignore it.\n");
                            }

                            if ($data['use_existing']) {
                                $AIquery = $AIquery->append('This is the existing text: ' . $component->getState() . "\n");
                            }

                            $AIquery = $AIquery->append('This is my query: ' . $data['prompt']);

                            try {
                                $response = FacadesAI::text(
                                    config('backstage.ai.providers')[$data['model']],
                                    $data['model'],
                                    $AIquery
                                );

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

    public function text(ProviderEnum $provider, string $model, string $prompt): Response
    {
        $response = Prism::text()
            ->using($provider, $model)
            ->withPrompt($prompt)
            ->generate();

        event(new CaptureAiRequest($response));

        return $response;
    }
}
