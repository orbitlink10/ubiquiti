<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

class MerchantCatalog
{
    public const COUNTRY = 'KE';

    public const CURRENCY = 'KES';

    public const DEFAULT_CONDITION = 'new';

    /**
     * Map an internal category slug to a Google product category string.
     *
     * @return array<string, string>
     */
    public static function googleCategories(): array
    {
        return [
            'ubiquiti-access-points' => 'Electronics > Communications > Networking > Access Points',
            'ubiquiti-switches' => 'Electronics > Communications > Networking > Switches',
            'ubiquiti-cloud-gateways' => 'Electronics > Communications > Networking > Routers',
            'ubiquiti-routers' => 'Electronics > Communications > Networking > Routers',
            'ubiquiti-airmax' => 'Electronics > Communications > Networking > Antennas',
            'ubiquiti-point-to-point' => 'Electronics > Communications > Networking > Antennas',
            'ubiquiti-airfiber' => 'Electronics > Communications > Networking > Antennas',
            'ubiquiti-uisp' => 'Electronics > Communications > Networking > Routers',
            'ubiquiti-antennas' => 'Electronics > Communications > Networking > Antennas',
            'ubiquiti-network-accessories' => 'Electronics > Communications > Networking > Networking Accessories',
        ];
    }

    /**
     * Map an internal category slug to a Merchant product_type hierarchy.
     *
     * @return array<string, string>
     */
    public static function productTypes(): array
    {
        return [
            'ubiquiti-access-points' => 'Networking > Ubiquiti > Access Points',
            'ubiquiti-switches' => 'Networking > Ubiquiti > Switches',
            'ubiquiti-cloud-gateways' => 'Networking > Ubiquiti > Cloud Gateways',
            'ubiquiti-routers' => 'Networking > Ubiquiti > Routers',
            'ubiquiti-airmax' => 'Networking > Ubiquiti > airMAX',
            'ubiquiti-point-to-point' => 'Networking > Ubiquiti > Point-to-Point',
            'ubiquiti-airfiber' => 'Networking > Ubiquiti > airFiber',
            'ubiquiti-uisp' => 'Networking > Ubiquiti > UISP',
            'ubiquiti-antennas' => 'Networking > Ubiquiti > Antennas',
            'ubiquiti-network-accessories' => 'Networking > Ubiquiti > Network Accessories',
        ];
    }

    public static function googleCategoryFor(Product $product): ?string
    {
        if ($override = trim((string) $product->google_product_category)) {
            return $override;
        }

        $slug = $product->category?->slug ?: $product->category?->parent?->slug;

        return self::googleCategories()[Str::slug((string) $slug)] ?? null;
    }

    public static function productTypeFor(Product $product): ?string
    {
        if ($override = trim((string) $product->product_type)) {
            return $override;
        }

        $slug = $product->category?->slug ?: $product->category?->parent?->slug;

        return self::productTypes()[Str::slug((string) $slug)] ?? null;
    }

    public static function mpn(Product $product): ?string
    {
        foreach ([$product->mpn, $product->sku, $product->model_number] as $candidate) {
            if ($value = self::cleanMpn($candidate)) {
                return $value;
            }
        }

        return null;
    }

    public static function cleanMpn(mixed $candidate): ?string
    {
        $value = trim((string) $candidate);

        if ($value === '') {
            return null;
        }

        $value = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $value) ?? $value);

        return $value !== '' ? $value : null;
    }

    public static function gtin(Product $product): ?string
    {
        $value = trim((string) $product->gtin);

        return $value !== '' ? $value : null;
    }

    public static function condition(Product $product): string
    {
        $condition = trim((string) $product->condition);

        return in_array($condition, ['new', 'used', 'refurbished'], true) ? $condition : self::DEFAULT_CONDITION;
    }

    /**
     * Merchant availability: in_stock, out_of_stock or preorder.
     */
    public static function availability(Product $product): string
    {
        if ($product->stock > 0) {
            return 'in_stock';
        }

        return 'out_of_stock';
    }

    public static function schemaAvailability(Product $product): string
    {
        return match (self::availability($product)) {
            'in_stock' => 'https://schema.org/InStock',
            'preorder' => 'https://schema.org/PreOrder',
            default => 'https://schema.org/OutOfStock',
        };
    }

    public static function conditionSchema(Product $product): string
    {
        return match (self::condition($product)) {
            'used' => 'https://schema.org/UsedCondition',
            'refurbished' => 'https://schema.org/RefurbishedCondition',
            default => 'https://schema.org/NewCondition',
        };
    }

    /**
     * Clean, Merchant-friendly title (no keyword stuffing).
     */
    public static function merchantTitle(Product $product): string
    {
        if ($title = trim((string) $product->merchant_title)) {
            return $title;
        }

        $brand = ProductSeo::brand($product);
        $model = ProductSeo::model($product);

        $type = match (UbiquitiSeoCatalog::productIntentSlug($product)) {
            'ubiquiti-access-points' => 'Access Point',
            'ubiquiti-switches' => 'Switch',
            'ubiquiti-cloud-gateways' => 'Cloud Gateway',
            'ubiquiti-routers' => 'Router',
            'ubiquiti-airmax' => 'airMAX Wireless',
            'ubiquiti-point-to-point' => 'Point-to-Point Wireless',
            'ubiquiti-airfiber' => 'airFiber',
            'ubiquiti-uisp' => 'UISP Equipment',
            'ubiquiti-antennas' => 'Antenna',
            default => null,
        };

        $parts = array_filter([$brand, $model, $type]);

        return implode(' ', $parts);
    }

    /**
     * Factual Merchant-friendly description (plain text).
     */
    public static function merchantDescription(Product $product): string
    {
        if ($description = trim((string) $product->merchant_description)) {
            return $description;
        }

        $description = $product->description
            ?: $product->meta_description
            ?: ProductSeo::displayName($product);

        $html = preg_replace('/<\/(p|br|div|h[1-6]|li|tr)>/i', ' ', (string) $description) ?? (string) $description;
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return Str::limit($text, 5000, '');
    }

    /**
     * Whether a product should be included in the Merchant feed.
     */
    public static function isFeedEligible(Product $product): bool
    {
        if ($product->status !== 'active') {
            return false;
        }

        if (! $product->vendor?->is_approved) {
            return false;
        }

        if ($product->include_in_merchant_feed === false) {
            return false;
        }

        if (trim((string) $product->name) === '') {
            return false;
        }

        if ($product->price === null) {
            return false;
        }

        return self::primaryImage($product) !== null;
    }

    public static function primaryImage(Product $product): ?string
    {
        $images = ProductImageCatalog::officialUrls($product);
        if ($images !== [] && ProductImageCatalog::isTrustedOfficialImageUrl($images[0])) {
            return $images[0];
        }

        foreach ($product->images as $image) {
            $url = $image->publicUrl();
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public static function additionalImages(Product $product): array
    {
        $images = ProductImageCatalog::officialUrls($product);

        return array_values(array_slice($images, 1, 10));
    }
}
