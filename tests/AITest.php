<?php

use Backstage\AI\AI;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\PendingRequest;
use Prism\Prism\Text\Response;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

function setupAIConfig(): void
{
    // Use string providers instead of Enum objects to survive config:cache
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

    config()->set('prism.providers.openai', [
        'url' => 'https://api.openai.com/v1',
        'api_key' => 'test-key',
    ]);
}

it('has correct prism namespace configuration', function () {
    // Verify the Prism facade exists with correct namespace
    expect(class_exists(Prism::class))->toBeTrue();
    expect(enum_exists(Provider::class))->toBeTrue();
    expect(class_exists(PrismException::class))->toBeTrue();
});

it('can retrieve provider from config as string', function () {
    setupAIConfig();

    $providers = config('backstage.ai.providers');
    $model = key($providers);
    $provider = $providers[$model] ?? null;

    expect($model)->toBe('gpt-5.1');
    expect($provider)->toBe('openai');
    expect($provider)->toBeString();
});

it('can create prism text request with string provider', function () {
    setupAIConfig();

    $providers = config('backstage.ai.providers');
    $model = key($providers);
    $provider = $providers[$model] ?? null;

    $pendingRequest = Prism::text()
        ->using($provider, $model)
        ->withPrompt('Test prompt');

    expect($pendingRequest)->toBeInstanceOf(PendingRequest::class);
    expect($pendingRequest->model())->toBe('gpt-5.1');
    expect($pendingRequest->providerKey())->toBe('openai');
});

it('can generate text with prism fake', function () {
    setupAIConfig();

    Prism::fake([
        new Response(
            steps: collect([]),
            text: 'Generated meta description for testing',
            finishReason: FinishReason::Stop,
            toolCalls: [],
            toolResults: [],
            usage: new Usage(10, 20),
            meta: new Meta(
                id: 'test-123',
                model: 'gpt-5.1',
            ),
            messages: collect([]),
        ),
    ]);

    $providers = config('backstage.ai.providers');
    $model = key($providers);
    $response = AI::generateText('Generate a meta description', $model);

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->text)->toBe('Generated meta description for testing');
});

it('throws exception when provider is not configured', function () {
    setupAIConfig();
    config()->set('backstage.ai.providers', []);

    AI::generateText('Generate something', 'non-existent-model');
})->throws(PrismException::class, 'AI provider not configured for model');

it('config survives serialization like config:cache', function () {
    setupAIConfig();

    // Simulate what happens during config:cache
    $originalConfig = config('backstage.ai.providers');

    // Serialize and unserialize (what config:cache effectively does)
    $serialized = serialize($originalConfig);
    $unserialized = unserialize($serialized);

    // Provider should still be a string after serialization
    expect($unserialized['gpt-5.1'])->toBe('openai');
    expect($unserialized['gpt-5.1'])->toBeString();
});

it('uses correct prism imports in AI class', function () {
    $reflection = new ReflectionClass(AI::class);
    $filename = $reflection->getFileName();
    $content = file_get_contents($filename);

    // Verify correct namespace imports
    expect($content)->toContain('use Prism\Prism\Exceptions\PrismException;');
    expect($content)->toContain('use Prism\Prism\Facades\Prism;');

    // Verify old namespace is not used
    expect($content)->not->toContain('EchoLabs\Prism');
});
