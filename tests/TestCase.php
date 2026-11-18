<?php

namespace Backstage\AI\Tests;

use Backstage\AI\AIServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Prism\Prism\PrismServiceProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Backstage\\AI\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FormsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            PrismServiceProvider::class,
            AIServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        // Set up AI config for testing
        config()->set('backstage.ai.providers', [
            'gpt-5.1' => 'openai',
        ]);

        config()->set('backstage.ai.action', [
            'label' => 'AI',
            'icon' => 'heroicon-o-sparkles',
            'modal' => [
                'heading' => 'Generate with AI',
            ],
        ]);

        config()->set('backstage.ai.configuration', [
            'max_tokens' => 100,
            'temperature' => 0.7,
        ]);

        // Set up Prism config for testing
        config()->set('prism.providers.openai', [
            'url' => 'https://api.openai.com/v1',
            'api_key' => 'test-key',
        ]);
    }
}
