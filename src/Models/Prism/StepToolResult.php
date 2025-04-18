<?php

namespace Backstage\AI\Models\Prism;

use Illuminate\Database\Eloquent\Model;

class StepToolResult extends Model
{
    public function getTable()
    {
        return 'prism_step_tool_results';
    }

    protected $fillable = ['prism_step_id', 'data'];

    protected $casts = ['data' => 'array'];

    public function step()
    {
        return $this->belongsTo(Step::class, 'prism_step_id', 'id');
    }
}
