<?php

namespace Backstage\AI\Models\Observers;

use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Backstage\AI\Models\Prompt;

class PromptObserver
{
    public function creating(Prompt $prompt)
    {
        $prompt->creator_id = Filament::auth()->id();
        $prompt->uuid = (string) Str::uuid();
    }
}
