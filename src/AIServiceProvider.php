<?php

namespace Backstage\AI;

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

        $package->hasMigrations(
            '1_create_ai_prompts_table',
        );

        if (file_exists($package->basePath('/../config/backstage/ai.php'))) {
            $package->hasConfigFile('backstage/ai');
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageBooted(): void
    {
        AI::registerMacro();
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }
}
