# Add a generative and assisting AI to your Filament app forms and fields.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/backstagephp/ai.svg?style=flat-square)](https://packagist.org/packages/backstagephp/ai)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/ai/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/backstagephp/ai/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/ai/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/backstagephp/ai/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/backstagephp/ai.svg?style=flat-square)](https://packagist.org/packages/backstagephp/ai)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require backstagephp/ai
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="ai-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="ai-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="ai-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentAI = new Backstage\AI();
echo $filamentAI->echoPhrase('Hello, backstagephp!');
```

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
