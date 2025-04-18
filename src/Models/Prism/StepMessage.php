<?php

namespace Backstage\AI\Models\Prism;

use Illuminate\Database\Eloquent\Model;

class StepMessage extends Model
{
    public function getTable()
    {
        return 'prism_step_messages';
    }

    protected $fillable = [
        'prism_step_id', 'role', 'content',
        'tool_calls', 'additional_content',
    ];

    protected $casts = [
        'tool_calls' => 'array',
        'additional_content' => 'array',
    ];

    public function step()
    {
        return $this->belongsTo(Step::class, 'prism_step_id', 'id');
    }
}
