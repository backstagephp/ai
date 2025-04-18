<?php

namespace Backstage\AI;

use Backstage\AI\Events\CaptureAiRequest;
use Backstage\AI\Listeners\RecordPrismResponse;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AIServiceProvider extends PackageServiceProvider
{
    public static string $name = 'ai';

    public static string $viewNamespace = 'ai';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('backstagephp/ai');
            });

        if (file_exists($package->basePath('/../config/backstage/ai.php'))) {
            $package->hasConfigFile('backstage/ai');
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }

        $package->hasMigrations($this->getMigrations());
    }

    public function packageBooted(): void
    {
        AI::registerMacro();

        Event::listen(CaptureAiRequest::class, RecordPrismResponse::class);

        Livewire::component('ai::pricing', \Backstage\AI\Components\Pricing::class);
        Livewire::component('ai::pricing-info', \Backstage\AI\Components\PricingInfo::class);

        FilamentView::registerRenderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, function (): Htmlable {
            return new HtmlString(Blade::render('@livewire("ai::pricing")'));
        });
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    protected function getMigrations(): array
    {
        $migrationsDir = $this->package->basePath('../database/migrations');

        $files = File::allFiles($migrationsDir);

        $migrations = [];

        foreach ($files as $file) {
            $file = str($file->getRelativePathname())
                ->replace('.php', '')
                ->toString();

            $migrations[] = $file;
        }

        return $migrations;
    }
}
