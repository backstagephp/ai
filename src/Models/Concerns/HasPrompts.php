<?php

namespace Backstage\AI\Models\Concerns;

use Backstage\AI\Models\Prompt;

trait HasPrompts
{
    public function prompts()
    {
        return $this->hasMany(Prompt::class, 'creator_id', 'id');
    }
}
