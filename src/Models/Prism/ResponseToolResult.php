<?php

namespace Backstage\AI\Models\Prism;

use Illuminate\Database\Eloquent\Model;

class ResponseToolResult extends Model
{
    public function getTable()
    {
        return 'prism_response_tool_results';
    }

    protected $fillable = ['prism_response_id', 'data'];

    protected $casts = ['data' => 'array'];

    public function response()
    {
        return $this->belongsTo(Response::class, 'prism_response_id', 'id');
    }
}
