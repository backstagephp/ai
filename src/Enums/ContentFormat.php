<?php

namespace Backstage\AI\Enums;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;

/**
 * The shape of the text a field stores, which decides both what the model is
 * asked to return and how its answer is cleaned before it is written.
 */
enum ContentFormat: string
{
    case Html = 'html';
    case Markdown = 'markdown';
    case PlainText = 'plain_text';

    public static function fromField(Field $field): self
    {
        return match (true) {
            $field instanceof RichEditor => self::Html,
            $field instanceof MarkdownEditor => self::Markdown,
            default => self::PlainText,
        };
    }

    public function instruction(): string
    {
        return match ($this) {
            self::Html => 'HTML. Use only these tags: p, br, strong, em, u, s, a, ul, ol, li, h2, h3. Put every paragraph in <p> and every list in <ul> or <ol> with <li> items. No Markdown, no code fences.',
            self::Markdown => 'Markdown. No code fence around the text.',
            self::PlainText => 'Plain text. No Markdown, no HTML.',
        };
    }
}
