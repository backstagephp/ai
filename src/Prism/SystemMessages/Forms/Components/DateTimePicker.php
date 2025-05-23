<?php

namespace Backstage\AI\Prism\SystemMessages\Forms\Components;

use Prism\Prism\ValueObjects\Messages\SystemMessage;

class DateTimePicker
{
    public static function ask(\Filament\Forms\Components\DateTimePicker $component): array
    {
        $format = $component->getFormat();

        $instructions = [
            new SystemMessage('You must return a date as output.'),
            new SystemMessage('The date format is: ' . $format),
        ];

        return $instructions;
    }
}
