<?php

namespace Backstage\AI\Models\Prism;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    public function getTable()
    {
        return 'prism_responses';
    }

    protected $fillable = [
        'text', 'finish_reason', 'prompt_tokens', 'completion_tokens',
        'cache_write_input_tokens', 'cache_read_input_tokens',
        'response_id', 'model',
    ];

    public function rateLimits()
    {
        return $this->hasMany(RateLimit::class, 'prism_response_id', 'id');
    }

    public function steps()
    {
        return $this->hasMany(Step::class, 'prism_response_id', 'id');
    }

    public function responseMessages()
    {
        return $this->hasMany(ResponseMessage::class, 'prism_response_id', 'id');
    }

    public function toolCalls()
    {
        return $this->hasMany(ResponseToolCall::class, 'prism_response_id', 'id');
    }

    public function toolResults()
    {
        return $this->hasMany(ResponseToolResult::class, 'prism_response_id', 'id');
    }
}
