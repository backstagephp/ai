<?php

namespace Backstage\AI\Models\Prism;

use Illuminate\Database\Eloquent\Model;

class ResponseMessage extends Model
{
    public function getTable()
    {
        return 'prism_response_messages';
    }

    protected $fillable = [
        'prism_response_id', 'role', 'content',
        'tool_calls', 'additional_content',
    ];

    protected $casts = [
        'tool_calls' => 'array',
        'additional_content' => 'array',
    ];

    public function response()
    {
        return $this->belongsTo(Response::class, 'prism_response_id', 'id');
    }
}
