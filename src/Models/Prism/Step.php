<?php

namespace Backstage\AI\Models\Prism;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    public function getTable()
    {
        return 'prism_steps';
    }

    protected $fillable = [
        'prism_response_id', 'text', 'finish_reason',
        'prompt_tokens', 'completion_tokens',
        'cache_write_input_tokens', 'cache_read_input_tokens',
        'response_id', 'model',
    ];

    public function response()
    {
        return $this->belongsTo(Response::class, 'prism_response_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(StepMessage::class, 'prism_step_id', 'id');
    }

    public function toolCalls()
    {
        return $this->hasMany(StepToolCall::class, 'prism_step_id', 'id');
    }

    public function toolResults()
    {
        return $this->hasMany(StepToolResult::class, 'prism_step_id', 'id');
    }
}
