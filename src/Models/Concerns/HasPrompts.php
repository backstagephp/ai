<?php

namespace Backstage\AI\Models\Concerns;

use Backstage\AI\Models\Prompt;

trait HasPrompts
{
    public function Prompts()
    {
        return $this->hasMany(Prompt::class, 'creator_id', 'id');
    }
}
