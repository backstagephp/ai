<?php

namespace Backstage\AI;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Backstage\AI\Resources\PromptResource;
use Filament\Support\Concerns\EvaluatesClosures;

class AIPlugin implements Plugin
{
    use EvaluatesClosures;

    public function getId(): string
    {
        return 'backstage-ai';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            PromptResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
