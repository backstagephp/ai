<?php

namespace Backstage\AI\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable;
use Backstage\AI\Models\Observers\PromptObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

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

    /**
     * @return Authenticatable|Model|null
     */
    public function creator()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'creator_id');
    }
}
