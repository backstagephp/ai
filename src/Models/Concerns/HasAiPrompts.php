<?php

namespace Backstage\AI\Models\Concerns;

use Backstage\AI\Models\AiPrompt;

trait HasAiPrompts
{
    public function aiPrompts()
    {
        return $this->hasMany(AiPrompt::class, 'creator_id', 'id');
    }
}
