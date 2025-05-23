<?php

namespace Backstage\AI\Models;

use Illuminate\Database\Eloquent\Model;
use Backstage\AI\Models\Observers\AiPromptObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(AiPromptObserver::class)]
class AiPrompt extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $fillable = [
        'id',
        'uuid',
        'creator_id',
        'name',
        'prompt',
    ];

    public function creator()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'creator_id');
    }
}
