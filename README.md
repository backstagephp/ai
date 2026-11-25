# Add a generative and assisting AI to your Filament app forms and fields.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/backstagephp/ai.svg?style=flat-square)](https://packagist.org/packages/backstagephp/ai)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/ai/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/backstagephp/ai/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/ai/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/backstagephp/ai/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/backstagephp/ai.svg?style=flat-square)](https://packagist.org/packages/backstagephp/ai)



Adds an AI button to Filament form fields. The button writes or rewrites the content of the field it sits on, through [Prism](https://prismphp.com), with any provider Prism supports.

## Installation

```bash
composer require backstage/ai
php artisan vendor:publish --tag="ai-config"
```

`config/backstage/ai.php` lists the models the button offers. The key is the model, the value the Prism provider. The first one is the default.

```php
return [
    'providers' => [
        'gpt-5.1' => 'openai',
    ],

    'action' => [
        'label' => 'AI',
        'icon' => 'heroicon-o-sparkles',
        'modal' => ['heading' => 'Generate with AI'],
    ],

    'configuration' => [
        'max_tokens' => 1000,
        'temperature' => 0.7,
    ],
];
```

Provider credentials are Prism's own configuration (`config/prism.php`).

## Usage

### A button that asks what to do

```php
RichEditor::make('body')->withAI();
```

The button opens a modal with an instruction field. On submit the model receives the instruction **and the text the field currently holds**, and its answer replaces the field. So "Rewrite this more formally" rewrites what is there, and on an empty field "Write a note that the customer wants a call on Monday" writes from the instruction alone.

A string pre-fills the instruction:

```php
Textarea::make('summary')->withAI('Summarise the text in three sentences');
```

### Saying what the field is for

The model knows nothing about your form. A `context` tells it what kind of text it is writing:

```php
RichEditor::make('body')
    ->withAI(context: 'An internal note about a customer. Short and factual.');

Textarea::make('intro')
    ->withAI(context: fn (Get $get) => 'The introduction of an article titled ' . $get('title'));
```

### A one-click button

Pass a closure to skip the modal. It builds the prompt, and that prompt is sent as it is; the field's own content is not added.

```php
TextInput::make('meta_title')
    ->maxLength(60)
    ->withAI(fn (Get $get) => 'Write an SEO title for: ' . $get('name'));
```

### What the model is told, and what is written

Every request carries a system prompt that makes the model fill a field instead of holding a conversation: reply with the field content only, treat the field's text as material and never as a message to answer, use only facts that were given.

The answer is cleaned before it reaches the field:

| Field | Asked for | Written |
| --- | --- | --- |
| `RichEditor` | HTML | Sanitized HTML with p, br, strong, em, u, s, a, ul, ol, li, h2 and h3. Plain or Markdown answers are converted. |
| `MarkdownEditor` | Markdown | Markdown |
| anything else | Plain text | Text without markup; the field's `maxLength()` is passed to the model |

Images, tables and custom blocks in a rich editor do not survive a rewrite.

The field is **not** changed, and the user gets a warning, when:

- the model had nothing to work with (it answers `NO_OUTPUT`, for instance for "rewrite this" on an empty field), or
- the answer was cut off by the token limit.

### Temperature and token limit

The modal's Configuration section sends both to the model, with the defaults from the config file. Keep `max_tokens` roomy: an answer that does not fit is refused rather than written half. Temperature is left out for models that reject it (gpt-5 and the o-series).

### Without a field

```php
use Backstage\AI\AI;

$response = AI::generateText('Return a JSON list of five keywords for: ...', 'gpt-5.1');

$response = AI::generateText($prompt, 'gpt-5.1', systemPrompt: 'Reply with the text only.', temperature: 0.2, maxTokens: 500);
```

With only a prompt and a model, nothing is added to the request.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mark van Eijk](https://github.com/markvaneijk)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
