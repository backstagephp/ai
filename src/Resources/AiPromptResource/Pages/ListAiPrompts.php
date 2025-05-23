<?php

namespace Backstage\AI\Resources\AiPromptResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Backstage\AI\Resources\AiPromptResource;

class ListAiPrompts extends ListRecords
{
    protected static string $resource = AiPromptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
