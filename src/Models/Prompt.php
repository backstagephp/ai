<?php

namespace Backstage\AI\Models;

use Illuminate\Database\Eloquent\Model;
use Backstage\AI\Models\Observers\PromptObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(PromptObserver::class)]
class Prompt extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $table = 'ai_prompts';

    protected $fillable = [
        'uuid',
        'creator_id',
        'name',
        'prompt',
        'model',
        'temperature',
        'max_tokens',
    ];

    protected $casts = [
        'temperature' => 'float',
        'max_tokens' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'creator_id');
    }
}
