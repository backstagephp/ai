<?php

namespace Backstage\AI\Tests;

use Backstage\AI\AIServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Prism\Prism\PrismServiceProvider;

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
            PrismServiceProvider::class,
            AIServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

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
