<?php

namespace Backstage\AI\Resources\PromptResource\Pages;

use Filament\Actions;
use Prism\Prism\Prism;
use Filament\Actions\Action;
use Backstage\AI\Models\Prompt;
use Prism\Prism\Enums\Provider;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Backstage\AI\Resources\PromptResource;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditPrompt extends EditRecord
{
    protected static string $resource = PromptResource::class;

    public $testResult;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('test_prompt')
                ->hiddenLabel()
                ->tooltip(fn(): string => __('Test Prompt'))
                ->icon('heroicon-o-light-bulb')
                ->color('gray')
                ->modal()
                ->modalIcon(fn($action): string => $action->getIcon())
                ->modalIconColor('primary')
                ->form([
                    Placeholder::make('system_prompt')
                        ->label(__('Defined system prompt'))
                        ->content(fn(): Htmlable => new HtmlString('<cite class="text-sm text-gray-500">' . $this->getRecord()->prompt . '</cite>')),

                    TextInput::make('input')
                        ->label(__('Input'))
                        ->required()
                        ->prefixIcon('heroicon-o-bookmark-square')
                        ->placeholder(__('Enter your input here...')),
                ])
                ->action(fn(array $data) => $this->testResultAction($data)),

            Actions\DeleteAction::make()
                ->label(fn(): string => __('Delete'))
                ->visible(fn(Prompt $record): bool => ! $record->trashed()),

            Actions\ForceDeleteAction::make()
                ->label(fn(): string => __('Force Delete'))
                ->visible(fn(Prompt $record): bool => $record->trashed()),

            Actions\RestoreAction::make()
                ->label(fn(): string => __('Restore'))
                ->visible(fn(Prompt $record): bool => $record->trashed()),
        ];
    }

    public function getRecordTitle(): string | Htmlable
    {
        return $this->record->name ?? __('AI Prompt');
    }

    public function testResultAction(array $data): void
    {
        $providerKey = collect(config('backstage.ai.providers'))
            ->filter(fn($models) => array_key_exists($this->getRecord()->model, $models))
            ->keys()
            ->first();

        $provider = Provider::from($providerKey);

        $response = Prism::text()
            ->using($provider, $this->getRecord()->model)
            ->withPrompt($data['input'])
            ->withSystemPrompt($this->getRecord()->prompt)
            ->asText();

        $this->testResult = $response->text;

        $this->replaceMountedAction('showResultAction');
    }

    public function showResultAction()
    {
        return Actions\Action::make('showResult')
            ->label(__('Test Result'))
            ->infolist([
                TextEntry::make('result')
                    ->label(__('Result'))
                    ->getStateUsing(fn(): Htmlable => new HtmlString('<cite class="text-sm text-gray-500">' . $this->testResult . '</cite>'))
                    ->hintIcon('heroicon-o-pencil-square')
                    ->hintColor('primary')
            ])
            ->modalSubmitAction(fn(Actions\StaticAction $action) => $action->hidden())
            ->modalCancelActionLabel(__('Close'))
            ->modalFooterActionsAlignment(Alignment::Center)
            ->modalSubmitAction(fn(StaticAction $action) => $action->label(__('Another')))
            ->action(fn() => $this->replaceMountedAction('test_prompt'));
    }
}
