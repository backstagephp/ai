<?php

namespace Backstage\AI\Prism\SystemMessages\Forms\Components;

use Prism\Prism\ValueObjects\Messages\SystemMessage;

class MarkdownEditor
{
    public static function ask(\Filament\Forms\Components\MarkdownEditor $component): array
    {
        $instructions = [
            new SystemMessage('You must return Markdown as output. This is the field that will implement the Markdown (state) that you will return: https://filamentphp.com/docs/3.x/forms/fields/markdown-editor.'),
            new SystemMessage("Don\'t return the markdown with markdown syntax like opening the markdown and closing it. For example: ```markdown... ```"),
        ];

        return $instructions;
    }
}
