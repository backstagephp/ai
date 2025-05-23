<?php

namespace Backstage\AI\Prism\SystemMessages\Forms\Components;

use Prism\Prism\ValueObjects\Messages\SystemMessage;

class RichEditor
{
    public static function ask(\Filament\Forms\Components\RichEditor $component): array
    {
        $instructions = [
            new SystemMessage('You must return pure HTML as output.'),
            new SystemMessage('This is the field that will implement the HTML (state) that you will return: https://filamentphp.com/docs/3.x/forms/fields/rich-editor.'),
            new SystemMessage('Do not return any <h1> tags.'),
        ];

        return $instructions;
    }
}
