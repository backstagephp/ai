<?php

use Backstage\AI\Tests\Fixtures\AIForm;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;

it('opens the generate modal when providers are configured as strings', function () {
    config()->set('backstage.ai.providers', ['gpt-5.1' => 'openai']);

    Livewire::test(AIForm::class)
        ->mountAction(TestAction::make('ai')->schemaComponent('description', schema: 'form'))
        ->assertActionMounted(TestAction::make('ai')->schemaComponent('description', schema: 'form'));
});

it('opens the generate modal when providers are configured as enums', function () {
    config()->set('backstage.ai.providers', ['gpt-5.1' => Provider::OpenAI]);

    Livewire::test(AIForm::class)
        ->mountAction(TestAction::make('ai')->schemaComponent('description', schema: 'form'))
        ->assertActionMounted(TestAction::make('ai')->schemaComponent('description', schema: 'form'));
});

it('requires a prompt instead of sending an empty one to the model', function () {
    $fake = Prism::fake([TextResponseFake::make()->withText('Generated')]);

    Livewire::test(AIForm::class)
        ->callAction(TestAction::make('ai')->schemaComponent('description', schema: 'form'), ['prompt' => null])
        ->assertHasActionErrors(['prompt' => 'required'])
        ->assertSet('data.description', null);

    $fake->assertCallCount(0);
});

it('writes the generated text into the field', function () {
    Prism::fake([TextResponseFake::make()->withText('Generated description')]);

    Livewire::test(AIForm::class)
        ->callAction(TestAction::make('ai')->schemaComponent('description', schema: 'form'), [
            'model' => 'gpt-5.1',
            'prompt' => 'Write a description',
        ])
        ->assertHasNoActionErrors()
        ->assertSet('data.description', 'Generated description');
});
