# Add a generative and assisting AI to your Filament app forms and fields.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/filament-ai.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/filament-ai)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/filament-ai/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vormkracht10/filament-ai/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/filament-ai/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/vormkracht10/filament-ai/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/filament-ai.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/filament-ai)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require vormkracht10/filament-ai
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-ai-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-ai-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-ai-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentAI = new Vormkracht10\FilamentAI();
echo $filamentAI->echoPhrase('Hello, vormkracht10!');
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
