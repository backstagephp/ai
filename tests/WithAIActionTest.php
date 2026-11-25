<?php

use Backstage\AI\FieldPrompt;
use Backstage\AI\Tests\Fixtures\AIForm;
use Filament\Actions\Testing\TestAction;
use Filament\Notifications\Notification;
use Livewire\Livewire;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\PrismFake;
use Prism\Prism\Testing\TextResponseFake;

function aiAction(string $field): TestAction
{
    return TestAction::make('ai')->schemaComponent($field, schema: 'form');
}

function fakeAnswer(string $text): PrismFake
{
    return Prism::fake([TextResponseFake::make()->withText($text)]);
}

function systemPromptOf(array $requests): string
{
    return $requests[0]->systemPrompts()[0]->content;
}

it('opens the generate modal when providers are configured as strings', function () {
    config()->set('backstage.ai.providers', ['gpt-5.1' => 'openai']);

    Livewire::test(AIForm::class)
        ->mountAction(aiAction('description'))
        ->assertActionMounted(aiAction('description'));
});

it('opens the generate modal when providers are configured as enums', function () {
    config()->set('backstage.ai.providers', ['gpt-5.1' => Provider::OpenAI]);

    Livewire::test(AIForm::class)
        ->mountAction(aiAction('description'))
        ->assertActionMounted(aiAction('description'));
});

it('requires a prompt instead of sending an empty one to the model', function () {
    $fake = fakeAnswer('Generated');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('description'), ['prompt' => null])
        ->assertHasActionErrors(['prompt' => 'required'])
        ->assertSet('data.description', null);

    $fake->assertCallCount(0);
});

it('writes the generated text into the field', function () {
    fakeAnswer('Generated description');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('description'), [
            'model' => 'gpt-5.1',
            'prompt' => 'Write a description',
        ])
        ->assertHasNoActionErrors()
        ->assertSet('data.description', 'Generated description');
});

// The bug this package shipped with: only the typed instruction reached the
// model, so "rewrite this" had nothing to rewrite and the model asked for the
// text instead, and that question was written into the field.
it('sends the current text of the field along with the instruction', function () {
    $fake = fakeAnswer('The customer called about a leak and asks for a visit next week.');

    Livewire::test(AIForm::class)
        ->fillForm(['description' => 'customer called, leak, wants visit next week'])
        ->callAction(aiAction('description'), ['prompt' => 'Rewrite this neatly'])
        ->assertSet('data.description', 'The customer called about a leak and asks for a visit next week.');

    $fake->assertRequest(function (array $requests): void {
        expect($requests[0]->prompt())
            ->toContain("<current_text>\ncustomer called, leak, wants visit next week\n</current_text>")
            ->toContain("<instruction>\nRewrite this neatly\n</instruction>");
    });
});

it('tells the model it is filling a field, not having a conversation', function () {
    $fake = fakeAnswer('Generated');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('description'), ['prompt' => 'Write something']);

    $fake->assertRequest(function (array $requests): void {
        expect($requests[0]->systemPrompts())->toHaveCount(1)
            ->and(systemPromptOf($requests))
            ->toContain('Reply with the new field content only')
            ->toContain('Never answer it')
            ->toContain(FieldPrompt::NO_OUTPUT)
            ->toContain('Format: Plain text.');
    });
});

it('treats field text that is a question as material, not as a question to answer', function () {
    $fake = fakeAnswer('Can you also include the window frames at the back?');

    Livewire::test(AIForm::class)
        ->fillForm(['description' => 'can u include the frames at the back to?'])
        ->callAction(aiAction('description'), ['prompt' => 'Fix the spelling']);

    $fake->assertRequest(function (array $requests): void {
        $prompt = $requests[0]->prompt();

        expect($prompt)->toContain('can u include the frames at the back to?')
            ->and(strpos($prompt, '<current_text>'))->toBeLessThan(strpos($prompt, '<instruction>'));
    });
});

it('says the field is empty rather than sending an empty block', function () {
    $fake = fakeAnswer('A new note.');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('description'), ['prompt' => 'Write a note about a late delivery']);

    $fake->assertRequest(function (array $requests): void {
        expect($requests[0]->prompt())
            ->toContain('The field is empty.')
            ->not->toContain('<current_text>');
    });
});

it('leaves the field alone when the model has nothing to work with', function (string $answer) {
    fakeAnswer($answer);

    Livewire::test(AIForm::class)
        ->fillForm(['description' => 'Keep me'])
        ->callAction(aiAction('description'), ['prompt' => 'Rewrite this'])
        ->assertSet('data.description', 'Keep me')
        ->assertNotified(Notification::make()
            ->title('Nothing to write')
            ->body('The AI had nothing to work with, so the field was left unchanged. Put text in the field first, or describe in the instruction what should be written.')
            ->warning());
})->with([
    'the sentinel' => 'NO_OUTPUT',
    'with a full stop' => 'NO_OUTPUT.',
    'in lower case' => 'no_output',
    'wrapped in a tag' => '<p>NO_OUTPUT</p>',
    'in a code fence' => "```\nNO_OUTPUT\n```",
    'an empty answer' => '   ',
]);

it('leaves the field alone when the answer was cut off', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('This sentence stops in the mid')
            ->withFinishReason(FinishReason::Length),
    ]);

    Livewire::test(AIForm::class)
        ->fillForm(['description' => 'Keep me'])
        ->callAction(aiAction('description'), ['prompt' => 'Write a long text'])
        ->assertSet('data.description', 'Keep me')
        ->assertNotified('The text was cut off');
});

it('sends the temperature and token limit from the modal to the model', function () {
    config()->set('backstage.ai.providers', ['gpt-4o-mini' => 'openai']);

    $fake = fakeAnswer('Generated');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('description'), [
            'prompt' => 'Write something',
            'temperature' => 0.2,
            'max_tokens' => 750,
        ])
        ->assertHasNoActionErrors();

    $fake->assertRequest(function (array $requests): void {
        expect($requests[0]->temperature())->toBe(0.2)
            ->and($requests[0]->maxTokens())->toBe(750);
    });
});

it('uses the configured defaults when the modal configuration is left untouched', function () {
    config()->set('backstage.ai.providers', ['gpt-4o-mini' => 'openai']);
    config()->set('backstage.ai.configuration', ['max_tokens' => 1200, 'temperature' => 0.4]);

    $fake = fakeAnswer('Generated');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('description'), ['prompt' => 'Write something']);

    $fake->assertRequest(function (array $requests): void {
        expect($requests[0]->temperature())->toBe(0.4)
            ->and($requests[0]->maxTokens())->toBe(1200);
    });
});

it('leaves the temperature out for a model that rejects it', function () {
    $fake = fakeAnswer('Generated');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('description'), [
            'model' => 'gpt-5.1',
            'prompt' => 'Write something',
            'temperature' => 0.2,
            'max_tokens' => 750,
        ]);

    $fake->assertRequest(function (array $requests): void {
        expect($requests[0]->temperature())->toBeNull()
            ->and($requests[0]->maxTokens())->toBe(750);
    });
});

it('asks a rich editor for html and sends its current html', function () {
    $fake = fakeAnswer('<p>The customer called about a leak.</p>');

    Livewire::test(AIForm::class)
        ->fillForm(['body' => '<p>customer called, <strong>leak</strong></p>'])
        ->callAction(aiAction('body'), ['prompt' => 'Rewrite this neatly'])
        ->assertSchemaStateSet(['body' => '<p>The customer called about a leak.</p>'], 'form');

    $fake->assertRequest(function (array $requests): void {
        expect(systemPromptOf($requests))->toContain('Format: HTML.')
            ->and($requests[0]->prompt())->toContain('<p>customer called, <strong>leak</strong></p>');
    });
});

it('turns a plain or markdown answer into paragraphs for a rich editor', function () {
    fakeAnswer("First paragraph with **bold**.\n\nSecond paragraph.");

    Livewire::test(AIForm::class)
        ->callAction(aiAction('body'), ['prompt' => 'Write two paragraphs'])
        ->assertSchemaStateSet(['body' => '<p>First paragraph with <strong>bold</strong>.</p><p>Second paragraph.</p>'], 'form');
});

it('strips a code fence and unsafe markup from a rich editor answer', function () {
    fakeAnswer("```html\n<p onclick=\"x()\">Safe <span class=\"x\">text</span></p><script>alert(1)</script>\n```");

    Livewire::test(AIForm::class)
        ->callAction(aiAction('body'), ['prompt' => 'Write something'])
        ->assertSchemaStateSet(['body' => '<p>Safe text</p>'], 'form');
});

it('sends a json rich editor as html and stores the answer as a document', function () {
    $fake = fakeAnswer('<p>Rewritten</p>');

    $component = Livewire::test(AIForm::class)
        ->fillForm(['document' => '<p>Original</p>'])
        ->callAction(aiAction('document'), ['prompt' => 'Rewrite this']);

    $fake->assertRequest(function (array $requests): void {
        expect($requests[0]->prompt())->toContain('<p>Original</p>');
    });

    $document = $component->instance()->form->getState()['document'];

    expect($document)->toBeArray()
        ->and($document['type'])->toBe('doc')
        ->and(json_encode($document))->toContain('Rewritten');
});

it('asks a markdown editor for markdown and keeps it as markdown', function () {
    $fake = fakeAnswer("# Title\n\nSome **bold** text.");

    Livewire::test(AIForm::class)
        ->callAction(aiAction('markdown'), ['prompt' => 'Write something'])
        ->assertSet('data.markdown', "# Title\n\nSome **bold** text.");

    $fake->assertRequest(function (array $requests): void {
        expect(systemPromptOf($requests))->toContain('Format: Markdown.');
    });
});

it('removes markup and wrapping quotes from a plain text answer', function () {
    fakeAnswer('"<p>Just the text</p>"');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('description'), ['prompt' => 'Write something'])
        ->assertSet('data.description', 'Just the text');
});

it('passes what the field is for to the model', function () {
    $fake = fakeAnswer('Generated');

    Livewire::test(AIForm::class)
        ->callAction(aiAction('note'), ['prompt' => 'Write something']);

    $fake->assertRequest(function (array $requests): void {
        expect(systemPromptOf($requests))->toContain('About this field: An internal note about a customer.');
    });
});

it('resolves a field context given as a closure', function () {
    $fake = fakeAnswer('Generated');

    Livewire::test(AIForm::class)
        ->fillForm(['title' => 'the kitchen job'])
        ->callAction(aiAction('dynamic'), ['prompt' => 'Write something']);

    $fake->assertRequest(function (array $requests): void {
        expect(systemPromptOf($requests))->toContain('About this field: A summary of the kitchen job');
    });
});

it('pre-fills the instruction with a default prompt given as a string', function () {
    Livewire::test(AIForm::class)
        ->mountAction(aiAction('summary'))
        ->assertSchemaStateSet(['prompt' => 'Summarise the text'], 'mountedActionSchema0');
});

it('opens the modal for a default prompt that happens to name a php function', function () {
    $fake = fakeAnswer('Generated');

    Livewire::test(AIForm::class)
        ->mountAction(aiAction('copied'))
        ->assertActionMounted(aiAction('copied'))
        ->assertSchemaStateSet(['prompt' => 'Copy'], 'mountedActionSchema0');

    $fake->assertCallCount(0);
});

it('generates in one click from a prompt closure, sending that prompt unchanged', function () {
    $fake = fakeAnswer('Kitchen renovation in Utrecht');

    Livewire::test(AIForm::class)
        ->fillForm(['description' => 'a kitchen renovation'])
        ->callAction(aiAction('title'))
        ->assertSet('data.title', 'Kitchen renovation in Utrecht');

    $fake->assertRequest(function (array $requests): void {
        expect($requests)->toHaveCount(1)
            ->and($requests[0]->prompt())->toBe('Write a title for: a kitchen renovation')
            ->and(systemPromptOf($requests))
            ->toContain('Reply with the new field content only')
            ->toContain('At most 60 characters.')
            ->and($requests[0]->temperature())->toBeNull()
            ->and($requests[0]->maxTokens())->toBeNull();
    });
});

// A number input is validated by the browser against min + n * step before the
// form is ever submitted. With a minimum of 1 and a step of 10, the default of
// 1000 was "not a valid value" and the Generate button did nothing.
it('offers defaults the browser accepts for the number inputs', function (int $maxTokens, float $temperature) {
    config()->set('backstage.ai.configuration', ['max_tokens' => $maxTokens, 'temperature' => $temperature]);

    $component = Livewire::test(AIForm::class)->mountAction(aiAction('description'));

    $fields = $component->instance()->getSchema('mountedActionSchema0')->getFlatFields(withHidden: true);

    foreach (['max_tokens' => $maxTokens, 'temperature' => $temperature] as $name => $default) {
        $steps = ($default - $fields[$name]->getMinValue()) / (float) $fields[$name]->getStep();

        expect(abs($steps - round($steps)))->toBeLessThan(0.000001, "{$name} default {$default} does not sit on a step");
    }
})->with([
    [1000, 0.7],
    [1024, 0.25],
    [100, 0.0],
    [4096, 1.0],
]);
