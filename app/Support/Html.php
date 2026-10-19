<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Cleans what the dashboard sends before it is stored or shown: rich text goes through an
 * allow-list HTML sanitiser, plain text loses control characters.
 */
class Html
{
    private static ?HtmlSanitizer $html = null;

    public static function text(mixed $value, int $max): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return mb_substr(trim((string) preg_replace('/\p{C}/u', '', (string) $value)), 0, $max);
    }

    public static function html(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        self::$html ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                // Paragraphs and headings may carry an alignment (TextAlignSanitizer keeps only that).
                ->allowElement('p', ['style'])
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('s')
                ->allowElement('h2', ['style'])
                ->allowElement('h3', ['style'])
                ->allowElement('h4', ['style'])
                ->withAttributeSanitizer(new TextAlignSanitizer)
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('blockquote')
                ->allowElement('hr')
                ->allowElement('a', ['href', 'target'])
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->forceAttribute('a', 'rel', 'noopener')
                ->withMaxInputLength(60000)
        );

        $clean = trim(self::$html->sanitize($value));

        return $clean === '<p></p>' ? '' : $clean;
    }
}
