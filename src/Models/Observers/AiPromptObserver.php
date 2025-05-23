<?php

namespace Backstage\AI\Models\Observers;

use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Backstage\AI\Models\AiPrompt;

class AiPromptObserver
{
    public function creating(AiPrompt $aiPrompt)
    {
        $aiPrompt->creator_id = Filament::auth()->id();
        $aiPrompt->uuid = (string) Str::uuid();
    }
}
