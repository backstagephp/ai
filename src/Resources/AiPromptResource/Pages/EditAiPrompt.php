<?php

namespace Backstage\AI\Resources\AiPromptResource\Pages;

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
            Actions\DeleteAction::make(),
        ];
    }

    public function getRecordTitle(): string|Htmlable
    {
        return $this->record?->name ?? __('AI Prompt');
    }
}
