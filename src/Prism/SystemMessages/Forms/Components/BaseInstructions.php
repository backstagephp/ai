<?php

namespace Backstage\AI\Prism\SystemMessages\Forms\Components;

use Prism\Prism\ValueObjects\Messages\SystemMessage;

class BaseInstructions
{
    public static function ask(\Filament\Forms\Components\Field $component): array
    {
        $baseInstructions = [
            new SystemMessage('You are a helpful assistant. That\'s inside a Filament form field. This is the state of the field: ' . json_encode($component->getState())),
            new SystemMessage('You must only return the value of the field.'),
            new SystemMessage('No yapping, no explanations, no extra text.'),
        ];

        return $baseInstructions;
    }
}
