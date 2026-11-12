<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

class ProductContent
{
    private const ALLOWED_TAGS = [
        'p',
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        'ul',
        'ol',
        'li',
        'a',
        'img',
        'h2',
        'h3',
        'pre',
        'code',
        'blockquote',
    ];

    private const DROP_TAGS = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
    ];

    /**
     * @var array<string, string[]>
     */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt'],
    ];

    public static function sanitizeRichText(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        if (!class_exists(DOMDocument::class)) {
            $fallback = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><a><img><h2><h3><pre><code><blockquote>');
            return trim($fallback) !== '' ? trim($fallback) : null;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><body>' . $html . '</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return null;
        }

        self::sanitizeChildren($body);

        $clean = '';
        for ($index = 0; $index < $body->childNodes->length; $index++) {
            $node = $body->childNodes->item($index);
            if ($node) {
                $clean .= $dom->saveHTML($node);
            }
        }

        $clean = trim($clean);
        return $clean !== '' ? $clean : null;
    }

    public static function sanitizeMetaDescription(?string $text): ?string
    {
        $text = self::plainText($text);
        return $text !== '' ? Str::limit($text, 255, '') : null;
    }

    public static function excerpt(?string $html, int $limit = 160): string
    {
        return Str::limit(self::plainText($html), $limit, '');
    }

    /**
     * Visible short-description helper: truncates at a word boundary and
     * appends an ellipsis so copy is never clipped mid-word.
     */
    public static function summary(?string $html, int $limit = 240): string
    {
        $text = self::plainText($html);
        if ($text === '') {
            return '';
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit);
        $lastSpace = mb_strrpos($cut, ' ');
        $cut = $lastSpace !== false && $lastSpace > (int) ($limit * 0.6)
            ? mb_substr($cut, 0, $lastSpace)
            : $cut;

        return rtrim($cut, " \t\n\r\0\x0B,.;:-").'…';
    }

    private static function plainText(?string $text): string
    {
        $plain = preg_replace('/\s+/u', ' ', strip_tags((string) $text)) ?? '';
        return trim($plain);
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                self::sanitizeElement($child);
            }
        }
    }

    private static function sanitizeElement(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        if (in_array($tag, self::DROP_TAGS, true)) {
            $element->parentNode?->removeChild($element);
            return;
        }

        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            self::sanitizeChildren($element);
            self::unwrapElement($element);
            return;
        }

        $allowedAttributes = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
        $attributes = [];

        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $attributeName) {
            if (!in_array(strtolower($attributeName), $allowedAttributes, true)) {
                $element->removeAttribute($attributeName);
            }
        }

        if ($tag === 'a') {
            $href = trim($element->getAttribute('href'));
            if (!self::isSafeHref($href)) {
                $element->removeAttribute('href');
                $element->removeAttribute('target');
                $element->removeAttribute('rel');
            } elseif (preg_match('/^https?:\/\//i', $href)) {
                $element->setAttribute('target', '_blank');
                $element->setAttribute('rel', 'noopener noreferrer');
            } else {
                $element->removeAttribute('target');
                $element->removeAttribute('rel');
            }
        }

        if ($tag === 'img') {
            $src = trim($element->getAttribute('src'));
            if (!self::isSafeSrc($src)) {
                $element->parentNode?->removeChild($element);
                return;
            }
        }

        self::sanitizeChildren($element);

        if (in_array($tag, ['h2', 'h3'], true)
            && trim(str_replace(["\xc2\xa0", "\xe2\x80\x8b"], '', $element->textContent)) === '') {
            $element->parentNode?->removeChild($element);
        }
    }

    private static function unwrapElement(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (!$parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private static function isSafeHref(string $href): bool
    {
        if ($href === '') {
            return false;
        }

        if (preg_match('/^(#|\/)/', $href) === 1) {
            return true;
        }

        return preg_match('/^(https?:|mailto:|tel:)/i', $href) === 1;
    }

    private static function isSafeSrc(string $src): bool
    {
        if ($src === '') {
            return false;
        }

        if (preg_match('/^(\/|https?:)/i', $src) === 1) {
            return true;
        }

        return preg_match('/^data:image\//i', $src) === 1;
    }
}
