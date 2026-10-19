<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Rich text may align paragraphs and headings (the editor's alignment buttons write
 * style="text-align: center"). Any other style is dropped.
 */
class TextAlignSanitizer implements AttributeSanitizerInterface
{
    public const ELEMENTS = ['p', 'h2', 'h3', 'h4'];

    public function getSupportedElements(): ?array
    {
        return self::ELEMENTS;
    }

    public function getSupportedAttributes(): ?array
    {
        return ['style'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        return preg_match('/^\s*text-align\s*:\s*(left|center|right|justify)\s*;?\s*$/i', $value, $m)
            ? 'text-align: '.strtolower($m[1])
            : null;
    }
}
