<?php

namespace Backstage\AI\Prism\SystemMessages\Forms\Components;

use Prism\Prism\ValueObjects\Messages\SystemMessage;

class Select
{
    public static function ask(\Filament\Forms\Components\Select $component): array
    {
        $instructions = [
            new SystemMessage('You must return a value from the select as output.'),
            new SystemMessage('The options are: ' . json_encode($component->getOptions())),
            new SystemMessage('You must return the key of the option as output.'),
        ];
        
        return $instructions;
    }
}
