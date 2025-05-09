<?php

namespace Backstage\AI\Prism\SystemMessages\Forms\Components;

use Prism\Prism\ValueObjects\Messages\SystemMessage;

class TextInput
{
    public static function ask(\Filament\Forms\Components\TextInput $component): array
    {
        $instructions = [];

        if ($component->isPassword()) {
            $instructions = [
                new SystemMessage('You must return a password as output.'),
            ];
        }

        if ($component->isEmail()) {
            $instructions = [
                new SystemMessage('You must return an email as output.'),
            ];
        }

        return $instructions;
    }
}
