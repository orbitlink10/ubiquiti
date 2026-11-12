<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MikrotikSeoCatalog
{
    public const ROUTER_AUTHORITY_SLUG = 'mikrotik-router-prices-in-kenya';

    /**
     * @return array<string, array{name: string, meta_description: string, description: string}>
     */
    public static function primaryCategories(): array
    {
        return [
            self::ROUTER_AUTHORITY_SLUG => [
                'name' => 'MikroTik Router Prices in Kenya',
                'meta_description' => 'Compare current MikroTik router prices in Kenya for home networks, offices, ISP deployments and enterprise routing.',
                'description' => '<p>Compare MikroTik routers available in Kenya by price, stock status and recommended use. This page is designed for buyers choosing RouterOS hardware for homes, offices, ISPs and branch networks.</p>',
            ],
            'mikrotik-switches' => [
                'name' => 'MikroTik Switches',
                'meta_description' => 'Shop MikroTik switches in Kenya for office networks, ISP aggregation, PoE deployments and rackmount switching.',
                'description' => '<p>Find MikroTik switches for access networks, office switching, fibre uplinks, PoE deployments and rack installations.</p>',
            ],
            'mikrotik-access-points' => [
                'name' => 'MikroTik Access Points',
                'meta_description' => 'Buy MikroTik access points in Kenya for homes, offices, hotels, campuses and managed wireless networks.',
                'description' => '<p>Browse MikroTik access points for indoor Wi-Fi, outdoor coverage and managed wireless deployments.</p>',
            ],
            'mikrotik-wireless' => [
                'name' => 'MikroTik Wireless Systems',
                'meta_description' => 'MikroTik wireless systems in Kenya for point-to-point links, outdoor broadband and ISP wireless deployments.',
                'description' => '<p>Explore MikroTik wireless systems for point-to-point links, outdoor access and long-distance network deployments.</p>',
            ],
            'mikrotik-lte-5g' => [
                'name' => 'MikroTik LTE & 5G Routers',
                'meta_description' => 'Shop MikroTik LTE and 5G routers in Kenya for mobile broadband, backup internet and remote sites.',
                'description' => '<p>Compare MikroTik LTE and 5G products for backup connectivity, remote sites, mobile offices and primary broadband where fibre is unavailable.</p>',
            ],
            'mikrotik-sfp-modules' => [
                'name' => 'MikroTik SFP Modules',
                'meta_description' => 'MikroTik SFP and SFP+ modules in Kenya for fibre uplinks, switches, routers and ISP networks.',
                'description' => '<p>Choose MikroTik SFP and SFP+ modules for fibre uplinks, switch interconnects and RouterOS network hardware.</p>',
            ],
            'mikrotik-antennas' => [
                'name' => 'MikroTik Antennas',
                'meta_description' => 'MikroTik antennas in Kenya for outdoor wireless links, LTE installations and point-to-point networks.',
                'description' => '<p>Find MikroTik antennas and related outdoor radio accessories for wireless links and LTE deployments.</p>',
            ],
            'mikrotik-accessories' => [
                'name' => 'MikroTik Accessories',
                'meta_description' => 'MikroTik accessories in Kenya including power supplies, mounts, cases and networking add-ons.',
                'description' => '<p>Browse MikroTik accessories for installation, powering, mounting and maintaining network equipment.</p>',
            ],
            'routeros' => [
                'name' => 'RouterOS',
                'meta_description' => 'RouterOS resources and MikroTik software-related products for routing, firewalling, VPN and network management.',
                'description' => '<p>RouterOS powers MikroTik routing, firewall, VPN, wireless and network management features across MikroTik hardware.</p>',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categoryTitles(): array
    {
        return [
            self::ROUTER_AUTHORITY_SLUG => 'MikroTik Router Prices in Kenya | Buy MikroTik Routers',
            'mikrotik-switches' => 'MikroTik Switch Prices in Kenya | MikroTik Switches',
            'mikrotik-access-points' => 'MikroTik Access Point Prices in Kenya',
            'mikrotik-wireless' => 'MikroTik Wireless Systems in Kenya | Point to Point & Outdoor',
            'mikrotik-lte-5g' => 'MikroTik LTE & 5G Routers in Kenya',
            'mikrotik-sfp-modules' => 'MikroTik SFP & SFP+ Modules in Kenya',
            'mikrotik-antennas' => 'MikroTik Antennas in Kenya',
            'mikrotik-accessories' => 'MikroTik Accessories in Kenya',
            'routeros' => 'RouterOS in Kenya | MikroTik',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function comparisonPages(): array
    {
        return [
            'rb760igs-vs-rb750gr3' => 'RB760iGS vs RB750Gr3',
            'rb4011-vs-rb5009' => 'RB4011 vs RB5009',
            'l009uigs-rm-vs-l009uigs-2haxd-in' => 'L009UiGS-RM vs L009UiGS-2HaxD-IN',
            'ccr2004-vs-ccr2116' => 'CCR2004 vs CCR2116',
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function comparisonProducts(): array
    {
        return [
            'rb760igs-vs-rb750gr3' => ['RB760iGS', 'RB750Gr3'],
            'rb4011-vs-rb5009' => ['RB4011', 'RB5009'],
            'l009uigs-rm-vs-l009uigs-2haxd-in' => ['L009UiGS-RM', 'L009UiGS-2HaxD-IN'],
            'ccr2004-vs-ccr2116' => ['CCR2004', 'CCR2116'],
        ];
    }

    /**
     * Comparison slugs whose two products currently exist in the active catalogue.
     *
     * @return array<int, string>
     */
    public static function resolvableComparisonSlugs(): array
    {
        $comparisons = self::comparisonProducts();
        if ($comparisons === []) {
            return [];
        }

        $products = Product::query()->active()->get(['id', 'name', 'slug', 'sku']);
        $resolvable = [];

        foreach ($comparisons as $slug => [$left, $right]) {
            if (self::findMatchingProduct($products, $left) && self::findMatchingProduct($products, $right)) {
                $resolvable[] = $slug;
            }
        }

        return $resolvable;
    }

    /**
     * Clean navigation label for a category (no keyword-stuffed suffixes).
     */
    public static function navLabel(Category $category): string
    {
        $slug = Str::slug($category->slug);

        if ($slug === self::ROUTER_AUTHORITY_SLUG || self::isRouterAuthorityCategory($category)) {
            return 'MikroTik Routers';
        }

        if ($mapped = self::primaryCategories()[$slug]['name'] ?? null) {
            return $mapped;
        }

        $name = trim((string) $category->name);
        $name = preg_replace('/\s*[-–|:]\s*price(s)?\s*in\s*kenya\s*$/iu', '', $name) ?? $name;
        $name = preg_replace('/\s*price(s)?\s*in\s*kenya\s*$/iu', '', $name) ?? $name;
        $name = preg_replace('/\s*for\s*sale\s*in\s*kenya\s*$/iu', '', $name) ?? $name;

        return $name !== '' ? $name : $category->name;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     */
    private static function findMatchingProduct($products, string $needle): bool
    {
        $needleSlug = str($needle)->lower()->replace('+', '-')->replace('_', '-')->slug()->toString();
        $needleLower = Str::lower($needle);

        return $products->contains(
            fn (Product $product): bool => Str::contains(Str::lower($product->name), $needleLower)
                || Str::contains(Str::lower((string) $product->sku), $needleLower)
                || Str::contains(Str::lower((string) $product->slug), $needleSlug)
        );
    }

    /**
     * @return array<string, string>
     */
    public static function legacyCategoryRedirects(): array
    {
        return [
            'mikrotik-routers' => self::ROUTER_AUTHORITY_SLUG,
            'mikrotik-router' => self::ROUTER_AUTHORITY_SLUG,
            'mikrotik-ethernet-routers' => self::ROUTER_AUTHORITY_SLUG,
            'mikrotik-ethernet-routers-price-in-kenya' => self::ROUTER_AUTHORITY_SLUG,
            'mikrotik-wired-routers-price-in-kenya' => self::ROUTER_AUTHORITY_SLUG,
            'mikrotik-routerboard-price-in-kenya' => self::ROUTER_AUTHORITY_SLUG,
            'mikrotik-wireless-for-home-and-office' => 'mikrotik-wireless',
            'mikrotik-wireless-systems-in-kenya' => 'mikrotik-wireless',
            'mikrotik-wireless-systems' => 'mikrotik-wireless',
            'mikrotik-switch-prices-in-kenya' => 'mikrotik-switches',
            'mikrotik-switches-in-kenya' => 'mikrotik-switches',
            'mikrotik-access-points-in-kenya' => 'mikrotik-access-points',
            'mikrotik-lte-routers' => 'mikrotik-lte-5g',
            'mikrotik-5g-routers' => 'mikrotik-lte-5g',
            'mikrotik-lte-routers-in-kenya' => 'mikrotik-lte-5g',
            'mikrotik-5g-routers-in-kenya' => 'mikrotik-lte-5g',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function topLevelCategoryRedirects(): array
    {
        return [
            'mikrotik-routers' => self::ROUTER_AUTHORITY_SLUG,
            'mikrotik-switches' => 'mikrotik-switches',
            'mikrotik-access-points' => 'mikrotik-access-points',
            'mikrotik-wireless' => 'mikrotik-wireless',
            'mikrotik-lte-5g' => 'mikrotik-lte-5g',
            'mikrotik-sfp-modules' => 'mikrotik-sfp-modules',
            'mikrotik-antennas' => 'mikrotik-antennas',
            'mikrotik-accessories' => 'mikrotik-accessories',
            'routeros' => 'routeros',
        ];
    }

    public static function isRouterAuthorityCategory(?Category $category): bool
    {
        if (! $category) {
            return false;
        }

        return in_array($category->slug, array_merge(
            [self::ROUTER_AUTHORITY_SLUG],
            array_keys(array_filter(
                self::legacyCategoryRedirects(),
                fn (string $target): bool => $target === self::ROUTER_AUTHORITY_SLUG
            ))
        ), true);
    }

    public static function isBroadMikrotikCategory(?Category $category): bool
    {
        if (! $category) {
            return false;
        }

        return in_array(Str::slug($category->slug), [
            'mikrotik',
            'mikrotik-products',
            'mikrotik-products-in-kenya',
            'mikrotik-kenya',
        ], true);
    }

    public static function targetSlugForLegacy(string $slug): ?string
    {
        return self::legacyCategoryRedirects()[Str::slug($slug)] ?? null;
    }

    public static function targetSlugForTopLevel(string $slug): ?string
    {
        return self::topLevelCategoryRedirects()[Str::slug($slug)] ?? null;
    }

    public static function productIntentSlug(Product $product): ?string
    {
        $text = Str::lower(implode(' ', [
            $product->name,
            $product->slug,
            $product->sku,
            $product->category?->name,
            $product->category?->slug,
        ]));

        if (Str::contains($text, ['routeros', 'license'])) {
            return 'routeros';
        }

        if (Str::contains($text, ['sfp', 'sfp+', 'qsfp', 'module', 'transceiver', 'dac'])) {
            return 'mikrotik-sfp-modules';
        }

        if (Str::contains($text, ['antenna', 'mant', 'sector'])) {
            return 'mikrotik-antennas';
        }

        if (Str::contains($text, ['lte', '5g', 'chateau', 'ltap', 'atl'])) {
            return 'mikrotik-lte-5g';
        }

        if (Str::contains($text, ['wireless', '60g', 'groove', 'lhg', 'sxt', 'netmetal', 'netbox', 'basebox', 'cube', 'cubeg', 'wireless wire'])) {
            return 'mikrotik-wireless';
        }

        if (Str::contains($text, ['access point', 'cap ', 'cap-', 'wap', 'hap ax', 'audience'])) {
            return 'mikrotik-access-points';
        }

        if (Str::contains($text, ['switch', 'crs', 'css'])) {
            return 'mikrotik-switches';
        }

        if (Str::contains($text, ['router', 'routerboard', 'rb', 'ccr', 'hex', 'l009', 'rb5009', 'rb4011'])) {
            return self::ROUTER_AUTHORITY_SLUG;
        }

        if (Str::contains($text, ['mikrotik'])) {
            return 'mikrotik-accessories';
        }

        return null;
    }

    public static function mikrotikProductsQuery(): Builder
    {
        return Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active()
            ->where(function (Builder $query): void {
                $query->where('name', 'like', '%MikroTik%')
                    ->orWhere('name', 'like', '%Mikrotik%')
                    ->orWhere('slug', 'like', '%mikrotik%')
                    ->orWhere('description', 'like', '%MikroTik%')
                    ->orWhere('description', 'like', '%Mikrotik%')
                    ->orWhereHas('category', function (Builder $categoryQuery): void {
                        $categoryQuery->where('name', 'like', '%MikroTik%')
                            ->orWhere('name', 'like', '%Mikrotik%')
                            ->orWhere('slug', 'like', '%mikrotik%');
                    });
            });
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public static function routerFaqItems(): array
    {
        return [
            [
                'question' => 'Which MikroTik router is best for home use in Kenya?',
                'answer' => 'For most homes and small offices, choose a model with enough Ethernet ports, Wi-Fi if required, and RouterOS features that match your internet speed and support needs.',
            ],
            [
                'question' => 'Are MikroTik router prices updated automatically?',
                'answer' => 'Prices on this page are generated from the product catalogue, so they change when the product price is updated in the store admin.',
            ],
            [
                'question' => 'Should I choose a router with SFP or SFP+ ports?',
                'answer' => 'Choose SFP or SFP+ when you need fibre uplinks, high-throughput aggregation, or a cleaner handoff to switches and ISP equipment.',
            ],
        ];
    }
}
