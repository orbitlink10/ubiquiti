<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductSeo
{
    public static function brand(Product $product): string
    {
        if ($brand = self::columnValue($product, 'brand')) {
            return self::normalizeBrand($brand);
        }

        $text = Str::lower($product->name.' '.$product->slug.' '.$product->sku.' '.$product->category?->name);

        if (Str::contains($text, ['ubiquiti', 'ubiquity', 'unifi', 'airmax', 'airfiber', 'uisp', 'edgerouter', 'litebeam', 'nanobeam', 'nanostation', 'powerbeam'])) {
            return 'Ubiquiti';
        }

        if (Str::contains($text, ['mikrotik', 'routeros'])) {
            return 'MikroTik';
        }

        return 'Ubiquiti';
    }

    public static function displayName(Product $product): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $product->name) ?? $product->name);
        $name = self::normalizeBrandSpelling($name);
        $name = trim(preg_replace('/\s*[-]\s*$/u', '', $name)) ?? $name;

        return $name !== '' ? $name : self::model($product);
    }

    public static function model(Product $product): string
    {
        if ($model = self::columnValue($product, 'model_number')) {
            return trim(preg_replace('/\s*[-]\s*$/u', '', self::normalizeBrandSpelling($model)) ?? $model);
        }

        $name = trim(preg_replace('/\s+/u', ' ', $product->name) ?? $product->name);
        $model = preg_replace('/^ubiquiti\s+/i', '', $name) ?? $name;
        $model = trim(preg_replace('/\s*[-]\s*$/u', '', self::normalizeBrandSpelling($model)) ?? $model);

        return trim($model) !== '' ? trim($model) : (string) ($product->sku ?: $product->slug);
    }

    public static function typeLabel(Product $product): string
    {
        return match (UbiquitiSeoCatalog::productIntentSlug($product)) {
            'ubiquiti-access-points' => 'Access Point',
            'ubiquiti-switches' => 'Switch',
            'ubiquiti-cloud-gateways' => 'Cloud Gateway',
            'ubiquiti-routers' => 'Router',
            'ubiquiti-airmax' => 'airMAX Wireless',
            'ubiquiti-point-to-point' => 'Point-to-Point Wireless',
            'ubiquiti-airfiber' => 'airFiber',
            'ubiquiti-uisp' => 'UISP Equipment',
            'ubiquiti-cameras' => 'Camera',
            'ubiquiti-nvr' => 'NVR',
            'ubiquiti-access-control' => 'Access Control',
            'ubiquiti-cloud-key' => 'Cloud Key',
            'ubiquiti-poe-injectors' => 'PoE Injector',
            'ubiquiti-antennas' => 'Antenna',
            'ubiquiti-fiber' => 'Fiber Equipment',
            default => 'Networking Equipment',
        };
    }

    public static function keyUse(Product $product): string
    {
        if ($keyUse = self::columnValue($product, 'key_use')) {
            return $keyUse;
        }

        return match (UbiquitiSeoCatalog::productIntentSlug($product)) {
            'ubiquiti-access-points' => 'Managed WiFi coverage for homes, offices and business networks',
            'ubiquiti-switches' => 'Switching, PoE power and UniFi network expansion',
            'ubiquiti-cloud-gateways' => 'UniFi routing, security and network management',
            'ubiquiti-routers' => 'Routing, VPN and internet gateway deployments',
            'ubiquiti-airmax' => 'Outdoor wireless links and WISP deployments',
            'ubiquiti-point-to-point' => 'Point-to-point wireless connectivity between sites',
            'ubiquiti-airfiber' => 'High-capacity wireless backhaul',
            'ubiquiti-uisp' => 'ISP routing, switching, wireless and fiber deployments',
            'ubiquiti-cameras' => 'UniFi Protect video security and surveillance',
            'ubiquiti-nvr' => 'UniFi Protect recording and video storage',
            'ubiquiti-access-control' => 'Managed door access control and intercom installations',
            'ubiquiti-poe-injectors' => 'Powering compatible PoE network devices',
            'ubiquiti-antennas' => 'Outdoor wireless coverage and link planning',
            'ubiquiti-fiber' => 'Fiber uplinks, modules and optical networking',
            default => 'Ubiquiti network installation and expansion',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function specs(Product $product): array
    {
        $specs = [
            'Model' => self::model($product),
            'Brand' => self::brand($product),
            'SKU' => (string) $product->sku,
            'Category' => $product->category?->name ?? 'Ubiquiti products',
            'Current price' => $product->price !== null ? ProductPricing::priceLabel($product) : '',
            'Availability' => ProductPricing::availabilityLabel($product),
        ];

        foreach (self::linesFromColumn($product, 'technical_specifications') as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));
                if ($key !== '' && $value !== '') {
                    $specs[$key] = $value;
                }
            }
        }

        return array_filter($specs, fn (?string $value): bool => trim((string) $value) !== '');
    }

    /**
     * @return array<int, string>
     */
    public static function useCases(Product $product): array
    {
        $custom = self::linesFromColumn($product, 'use_cases');
        if ($custom !== []) {
            return $custom;
        }

        return match (UbiquitiSeoCatalog::productIntentSlug($product)) {
            'ubiquiti-access-points' => ['Home and office WiFi coverage', 'Hotel, school and business wireless networks', 'Managed UniFi deployments'],
            'ubiquiti-switches' => ['PoE for access points and cameras', 'Office LAN switching', 'Network rack expansion and uplinks'],
            'ubiquiti-cloud-gateways' => ['UniFi network control', 'Business internet gateways', 'VPN and security gateway deployments'],
            'ubiquiti-airmax', 'ubiquiti-point-to-point' => ['Point-to-point wireless links', 'WISP customer connections', 'Remote site connectivity'],
            'ubiquiti-cameras' => ['Home and business CCTV', 'UniFi Protect camera systems', 'Outdoor and indoor security monitoring'],
            'ubiquiti-access-control' => ['Door access control', 'Office entry management', 'Intercom and credential-based access'],
            default => ['Home network upgrades', 'Business network deployments', 'ISP and installer projects'],
        };
    }

    /**
     * @return array<int, string>
     */
    public static function applications(Product $product): array
    {
        return self::linesFromColumn($product, 'recommended_applications') ?: self::useCases($product);
    }

    public static function compatibility(Product $product): string
    {
        return self::columnValue($product, 'compatibility')
            ?: 'Works with compatible Ubiquiti UniFi, UISP or standard Ethernet networking equipment. Confirm controller, power and mounting requirements before purchase.';
    }

    public static function powerRequirements(Product $product): string
    {
        return self::columnValue($product, 'power_requirements')
            ?: 'Check the product label or manufacturer datasheet for exact input voltage, PoE support and power adapter requirements.';
    }

    public static function warrantyInfo(Product $product): string
    {
        return self::columnValue($product, 'warranty_info')
            ?: 'Warranty terms depend on the seller and product condition. Confirm warranty coverage before checkout or quotation approval.';
    }

    public static function deliveryInfo(Product $product): string
    {
        return self::columnValue($product, 'delivery_info')
            ?: 'Delivery options and timelines are confirmed during checkout or direct enquiry based on stock location and destination.';
    }

    public static function paymentInfo(Product $product): string
    {
        return self::columnValue($product, 'payment_info')
            ?: 'Payment options are confirmed at checkout or through the seller before dispatch.';
    }

    public static function chooseAnotherModel(Product $product): ?string
    {
        return self::columnValue($product, 'choose_another_model');
    }

    /**
     * @return array<int, string>
     */
    public static function whatsInBox(Product $product): array
    {
        return self::linesFromColumn($product, 'whats_in_box')
            ?: [self::brand($product).' '.self::model($product).' unit', 'Included accessories as supplied by the seller or manufacturer package'];
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public static function faqs(Product $product): array
    {
        $custom = self::faqItems($product);
        if ($custom !== []) {
            return $custom;
        }

        $displayName = self::displayName($product);

        return [
            [
                'question' => 'Is '.$displayName.' available in Kenya?',
                'answer' => $product->stock > 0
                    ? $displayName.' is currently listed as available. Stock can change, so confirm availability before placing a large order.'
                    : $displayName.' needs current availability confirmation from the seller before ordering.',
            ],
            [
                'question' => 'What is the current price of '.$displayName.'?',
                'answer' => $product->price !== null
                    ? 'The current listed price is KSh '.number_format((float) $product->price, 2).'. Prices are generated from the product catalogue and may change when inventory is updated.'
                    : 'Price is not published yet. Contact the seller to confirm current pricing before ordering.',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public static function comparisonLinks(Product $product): array
    {
        $labels = UbiquitiSeoCatalog::comparisonPages();
        $links = [];
        $haystack = Str::lower($product->name.' '.$product->slug.' '.$product->sku);

        foreach (UbiquitiSeoCatalog::comparisonProducts() as $slug => [$left, $right]) {
            $leftMatch = self::containsProductNeedle($haystack, $left);
            $rightMatch = self::containsProductNeedle($haystack, $right);

            if (! $leftMatch && ! $rightMatch) {
                continue;
            }

            $otherNeedle = $leftMatch ? $right : $left;
            $otherProduct = Product::query()
                ->active()
                ->where(function ($query) use ($otherNeedle): void {
                    $needleSlug = Str::slug(str_replace('+', ' plus ', $otherNeedle));
                    $query->where('name', 'like', '%'.$otherNeedle.'%')
                        ->orWhere('sku', 'like', '%'.$otherNeedle.'%')
                        ->orWhere('slug', 'like', '%'.$needleSlug.'%');
                })
                ->first();

            if ($otherProduct) {
                $links[] = [
                    'label' => $labels[$slug] ?? $slug,
                    'url' => route('comparison.show', $slug),
                ];
            }
        }

        return $links;
    }

    public static function youtubeVideoId(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (preg_match('/(?:youtube\.com|youtube-nocookie\.com)\/(?:watch\?(?:[^#]*&)?v=|embed\/|shorts\/)([A-Za-z0-9_-]{6,})/', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('/youtu\.be\/([A-Za-z0-9_-]{6,})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function youtubeEmbedUrl(?string $url): ?string
    {
        $videoId = self::youtubeVideoId($url);

        return $videoId ? 'https://www.youtube-nocookie.com/embed/'.$videoId : null;
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    private static function faqItems(Product $product): array
    {
        if (! self::columnReady($product, 'faq_items') || ! is_array($product->faq_items)) {
            return [];
        }

        $items = [];
        foreach ($product->faq_items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question !== '' && $answer !== '') {
                $items[] = ['question' => $question, 'answer' => $answer];
            }
        }

        return $items;
    }

    /**
     * @return array<int, string>
     */
    private static function linesFromColumn(Product $product, string $column): array
    {
        $value = self::columnValue($product, $column);
        if (! $value) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $line): string => trim(strip_tags($line)),
            preg_split('/\r\n|\r|\n/', $value) ?: []
        )));
    }

    private static function columnValue(Product $product, string $column): ?string
    {
        if (! self::columnReady($product, $column)) {
            return null;
        }

        $value = trim((string) ($product->{$column} ?? ''));

        return $value !== '' ? $value : null;
    }

    private static function columnReady(Product $product, string $column): bool
    {
        static $cache = [];
        $key = $product->getTable().'.'.$column;

        return $cache[$key] ??= Schema::hasColumn($product->getTable(), $column);
    }

    private static function normalizeBrand(string $brand): string
    {
        return self::normalizeBrandSpelling($brand);
    }

    private static function normalizeBrandSpelling(string $value): string
    {
        $value = preg_replace('/\bubiquity\b/i', 'Ubiquiti', $value) ?? $value;
        $value = preg_replace('/\bubiquiti\b/i', 'Ubiquiti', $value) ?? $value;
        $value = preg_replace('/\bunifi\b/i', 'UniFi', $value) ?? $value;
        $value = preg_replace('/\bairmax\b/i', 'airMAX', $value) ?? $value;
        $value = preg_replace('/\bairfiber\b/i', 'airFiber', $value) ?? $value;
        $value = preg_replace('/\buisp\b/i', 'UISP', $value) ?? $value;
        $value = preg_replace('/\bmikrotik\b/i', 'MikroTik', $value) ?? $value;

        return $value;
    }

    private static function containsProductNeedle(string $haystack, string $needle): bool
    {
        $needleLower = Str::lower($needle);
        $needleSlug = Str::slug(str_replace('+', ' plus ', $needle));

        return Str::contains($haystack, $needleLower) || Str::contains($haystack, $needleSlug);
    }
}
