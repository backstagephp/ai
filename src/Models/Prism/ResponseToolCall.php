<?php

namespace Backstage\AI\Models\Prism;

use Illuminate\Database\Eloquent\Model;

class ResponseToolCall extends Model
{
    public function getTable()
    {
        return 'prism_response_tool_calls';
    }

    protected $fillable = ['prism_response_id', 'data'];

    protected $casts = ['data' => 'array'];

    public function response()
    {
        return $this->belongsTo(Response::class, 'prism_response_id', 'id');
    }
}
