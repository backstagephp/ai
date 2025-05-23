<?php

namespace Backstage\AI\Resources\PromptResource\Pages;

use Backstage\AI\Resources\PromptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrompts extends ListRecords
{
    protected static string $resource = PromptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
