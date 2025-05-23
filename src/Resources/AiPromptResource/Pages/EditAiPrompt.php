<?php

namespace Backstage\AI\Resources\AiPromptResource\Pages;

use Backstage\AI\Models\AiPrompt;
use Backstage\AI\Resources\AiPromptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditAiPrompt extends EditRecord
{
    protected static string $resource = AiPromptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('Delete'))
                ->visible(fn(AiPrompt $record): bool => ! $record->trashed()),

            Actions\ForceDeleteAction::make()
                ->label(__('Force Delete'))
                ->visible(fn(AiPrompt $record): bool => $record->trashed()),

            Actions\RestoreAction::make()
                ->label(__('Restore'))
                ->visible(fn(AiPrompt $record): bool => $record->trashed()),
        ];
    }

    public function getRecordTitle(): string|Htmlable
    {
        return $this->record?->name ?? __('AI Prompt');
    }
}
