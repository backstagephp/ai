<?php

namespace Backstage\AI;

use Backstage\AI\Enums\ContentFormat;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerAction;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Reads what a field holds before the model is asked, and cleans what the model
 * returns before it is written back.
 *
 * A model's answer is never trusted as field content: it may be wrapped in a
 * code fence, be Markdown where HTML was asked for, carry tags the editor does
 * not know, or be the NO_OUTPUT sentinel. `clean()` returns null when there is
 * nothing that should be written, so the caller leaves the field alone.
 */
class FieldContent
{
    public static function read(Field $field): string
    {
        $state = $field->getState();

        if ($field instanceof RichEditor && is_array($state)) {
            return $field->getTipTapEditor()->setContent($state)->getHtml();
        }

        return is_scalar($state) ? (string) $state : '';
    }

    public static function isBlank(?string $content): bool
    {
        return self::text((string) $content) === '';
    }

    public static function clean(?string $output, ContentFormat $format): ?string
    {
        $output = self::withoutCodeFence(trim((string) $output));

        if (self::isBlank($output) || self::isNoOutput($output)) {
            return null;
        }

        return match ($format) {
            ContentFormat::Html => self::html($output),
            ContentFormat::Markdown => $output,
            ContentFormat::PlainText => self::plainText($output),
        };
    }

    protected static function text(string $content): string
    {
        return trim(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5), " \t\n\r\0\x0B\u{A0}");
    }

    protected static function isNoOutput(string $output): bool
    {
        return preg_match('/\bNO[_ ]OUTPUT\b/i', strip_tags($output)) === 1;
    }

    protected static function withoutCodeFence(string $output): string
    {
        if (preg_match('/\A```[a-zA-Z]*\s*\n(.*?)\n?```\z/s', $output, $matches) === 1) {
            return trim($matches[1]);
        }

        return $output;
    }

    protected static function html(string $output): ?string
    {
        if (strip_tags($output) === $output) {
            $output = Str::markdown($output, [
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
                'renderer' => ['soft_break' => "<br>\n"],
            ]);
        }

        // The sanitizer writes a plus sign as an entity; in the UTF-8 documents
        // this ends up in, the character itself is just as safe and readable.
        $html = str_replace('&#43;', '+', self::sanitizer()->sanitize($output));

        $html = self::withoutWhitespaceBetweenBlocks(self::withRealLists($html));

        return self::isBlank($html) ? null : $html;
    }

    /**
     * Models regularly fake a list inside a paragraph, as lines that start with
     * a dash or a number and end in a line break. An editor shows those as plain
     * text, so they are turned into the list they were meant to be.
     */
    protected static function withRealLists(string $html): string
    {
        return (string) preg_replace_callback('/<p>(.*?)<\/p>/s', function (array $matches): string {
            $lines = array_values(array_filter(
                array_map('trim', preg_split('/<br\s*\/?>/i', $matches[1]) ?: []),
                fn (string $line): bool => $line !== '',
            ));

            if (count($lines) < 2) {
                return $matches[0];
            }

            foreach (['ul' => '/^(?:[-*•·–—])\s+(.+)$/su', 'ol' => '/^\d+[.)]\s+(.+)$/su'] as $tag => $pattern) {
                $items = [];

                foreach ($lines as $line) {
                    if (preg_match($pattern, $line, $item) !== 1) {
                        continue 2;
                    }

                    $items[] = '<li>' . trim($item[1]) . '</li>';
                }

                return "<{$tag}>" . implode('', $items) . "</{$tag}>";
            }

            return $matches[0];
        }, $html);
    }

    protected static function withoutWhitespaceBetweenBlocks(string $html): string
    {
        $blocks = 'p|ul|ol|li|h2|h3';

        $html = (string) preg_replace("/\s+(?=<\/?(?:{$blocks})>)/", '', $html);

        return trim((string) preg_replace("/(<\/(?:{$blocks})>|<(?:ul|ol)>)\s+/", '$1', $html));
    }

    protected static function plainText(string $output): ?string
    {
        $text = self::text($output);

        foreach ([['"', '"'], ['“', '”'], ["'", "'"]] as [$open, $close]) {
            if (
                mb_strlen($text) > 1
                && str_starts_with($text, $open)
                && str_ends_with($text, $close)
                && ! str_contains(mb_substr($text, 1, -1), $open)
                && ! str_contains(mb_substr($text, 1, -1), $close)
            ) {
                $text = trim(mb_substr($text, 1, -1));

                break;
            }
        }

        return $text === '' ? null : $text;
    }

    protected static function sanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig)
            ->defaultAction(HtmlSanitizerAction::Block)
            ->withMaxInputLength(500000)
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowElement('a', ['href']);

        foreach (['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'h2', 'h3'] as $element) {
            $config = $config->allowElement($element);
        }

        foreach (['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template', 'svg', 'math', 'head', 'title', 'form', 'button', 'select', 'textarea', 'input'] as $element) {
            $config = $config->dropElement($element);
        }

        return new HtmlSanitizer($config);
    }
}
