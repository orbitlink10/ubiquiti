<?php

namespace App\Support;

use App\Models\Product;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;

class CatalogContent
{
    /** Replace inherited retailer referrals with matching products in our catalogue. */
    public static function render(?string $html): ?string
    {
        $html = ProductContent::sanitizeRichText($html);
        if (! $html) {
            return null;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($dom);
        $products = null;

        foreach ($xpath->query('//a[@href]') as $anchor) {
            $host = Str::lower((string) parse_url($anchor->getAttribute('href'), PHP_URL_HOST));
            if (! in_array(preg_replace('/^www\./', '', $host), ['orbitlinksolutions.co.ke', 'tawa.co.ke', 'reisenseo.com', 'mikrotikkenya.co.ke'], true)) {
                continue;
            }

            $products ??= Product::query()->active()->get();
            $label = self::key(preg_replace('/\s+at\s+.*$/i', '', $anchor->textContent));
            $match = $products->first(function (Product $product) use ($label): bool {
                foreach ([$product->model_number, $product->sku, $product->name] as $name) {
                    $key = self::key((string) $name);
                    if ($key !== '' && $key === $label) {
                        return true;
                    }
                }

                return false;
            });

            if ($match) {
                $anchor->setAttribute('href', CanonicalUrl::route('product.show', $match));
                $anchor->removeAttribute('target');
                $anchor->removeAttribute('rel');
                while ($anchor->firstChild) {
                    $anchor->removeChild($anchor->firstChild);
                }
                $anchor->appendChild($dom->createTextNode(ProductSeo::displayName($match)));
            } else {
                // Keep useful prose when the referenced model is not sold here.
                $anchor->parentNode->replaceChild($dom->createTextNode($anchor->textContent), $anchor);
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $clean = '';
        foreach ($body->childNodes as $node) {
            $clean .= $dom->saveHTML($node);
        }

        return trim($clean);
    }

    private static function key(string $name): string
    {
        $name = preg_replace('/\b(ubiquiti|ubiquity|unifi|price in kenya|wi-?fi [67]|access point|indoor scalable)\b/i', '', $name);

        return preg_replace('/[^a-z0-9+]/', '', Str::lower($name));
    }
}
