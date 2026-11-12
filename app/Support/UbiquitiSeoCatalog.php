<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UbiquitiSeoCatalog
{
    public const ROUTER_AUTHORITY_SLUG = 'ubiquiti-routers';

    /**
     * @return array<string, array{name: string, h1: string, focus_keyword: string, meta_description: string, intro: string, description: string, seo_content: string, faq_items: array<int, array{question: string, answer: string}>}>
     */
    public static function primaryCategories(): array
    {
        return [
            'ubiquiti-access-points' => [
                'name' => 'Ubiquiti Access Points',
                'h1' => 'Ubiquiti Access Points in Kenya',
                'focus_keyword' => 'Ubiquiti Access Points Kenya',
                'meta_description' => 'Shop Ubiquiti access points in Kenya for homes, offices, hotels, campuses and managed WiFi networks.',
                'intro' => 'Compare Ubiquiti UniFi access points for indoor WiFi, outdoor coverage, mesh networks and high-density deployments in Kenya.',
                'description' => '<p>Browse Ubiquiti access points for home, office, hotel, school and enterprise WiFi deployments in Kenya.</p>',
                'seo_content' => '<h2>Choosing Ubiquiti access points in Kenya</h2><p>Use this category to compare UniFi access points by WiFi generation, indoor or outdoor use, mounting style and deployment size. Keep verified specifications, prices and stock status in the product records so buyers can compare models accurately.</p>',
                'faq_items' => [
                    ['question' => 'Which Ubiquiti access point should I buy in Kenya?', 'answer' => 'Choose based on the WiFi standard, installation location, expected number of users, PoE requirements and whether the network will be managed with UniFi.'],
                    ['question' => 'Do UniFi access points need PoE?', 'answer' => 'Many UniFi access points are powered by PoE. Confirm the exact PoE standard for the specific model before purchase.'],
                ],
            ],
            'ubiquiti-switches' => [
                'name' => 'Ubiquiti UniFi Switches',
                'h1' => 'Ubiquiti Switches in Kenya',
                'focus_keyword' => 'Ubiquiti Switches Kenya',
                'meta_description' => 'Shop Ubiquiti UniFi switches in Kenya including PoE, non-PoE, 8-port, 16-port, 24-port, 48-port, 2.5G and 10G switches.',
                'intro' => 'Find Ubiquiti switches for access points, CCTV, office LANs, fibre uplinks and larger UniFi networks.',
                'description' => '<p>Compare Ubiquiti switches for PoE devices, office switching, aggregation and enterprise network expansion.</p>',
                'seo_content' => '<h2>Ubiquiti switch selection guide</h2><p>When choosing a UniFi switch, check the port count, PoE budget, uplink speed, rack or desktop format and future expansion needs. Product records should hold verified prices, stock and technical details.</p>',
                'faq_items' => [
                    ['question' => 'Which Ubiquiti switch is best for access points?', 'answer' => 'Use a PoE switch with enough power budget and ports for the number of UniFi access points you plan to install.'],
                    ['question' => 'Should I choose a 24-port or 48-port Ubiquiti switch?', 'answer' => 'Choose based on current device count, spare capacity, rack space and uplink requirements.'],
                ],
            ],
            'ubiquiti-cloud-gateways' => [
                'name' => 'Ubiquiti Cloud Gateways',
                'h1' => 'Ubiquiti Cloud Gateways in Kenya',
                'focus_keyword' => 'Ubiquiti Cloud Gateway Kenya',
                'meta_description' => 'Compare Ubiquiti Cloud Gateway devices in Kenya for UniFi routing, security, VPN, application hosting and network management.',
                'intro' => 'Choose Ubiquiti Cloud Gateways for UniFi network management, routing and security in homes, offices and business networks.',
                'description' => '<p>Ubiquiti Cloud Gateways combine routing and UniFi management for modern networks.</p>',
                'seo_content' => '<h2>Ubiquiti Cloud Gateway buying notes</h2><p>Match the gateway to your internet speed, number of managed UniFi devices, VPN needs and security features. Keep exact throughput and storage details in the product specifications after verification.</p>',
                'faq_items' => [
                    ['question' => 'What does a Ubiquiti Cloud Gateway do?', 'answer' => 'It handles routing and UniFi management features for compatible UniFi networks. Exact capabilities depend on the model.'],
                ],
            ],
            'ubiquiti-routers' => [
                'name' => 'Ubiquiti Routers',
                'h1' => 'Ubiquiti Routers in Kenya',
                'focus_keyword' => 'Ubiquiti Routers Kenya',
                'meta_description' => 'Shop Ubiquiti routers in Kenya for UniFi networks, ISP deployments, VPN, multi-WAN and business internet gateways.',
                'intro' => 'Compare Ubiquiti routers and gateways for homes, offices, ISPs, VPN networks and multi-WAN internet setups.',
                'description' => '<p>Find Ubiquiti routing equipment for UniFi, UISP, ISP and business networks in Kenya.</p>',
                'seo_content' => '<h2>Choosing a Ubiquiti router in Kenya</h2><p>Confirm WAN speed, LAN port count, VPN needs, UniFi or UISP management requirements and failover features before choosing a Ubiquiti router or gateway.</p>',
                'faq_items' => self::routerFaqItems(),
            ],
            'ubiquiti-airmax' => [
                'name' => 'Ubiquiti airMAX',
                'h1' => 'Ubiquiti airMAX in Kenya',
                'focus_keyword' => 'Ubiquiti airMAX Kenya',
                'meta_description' => 'Shop Ubiquiti airMAX equipment in Kenya including LiteBeam, NanoBeam, NanoStation, PowerBeam, Rocket and antennas.',
                'intro' => 'Browse airMAX radios and antennas for outdoor wireless links, WISP networks and point-to-multipoint deployments.',
                'description' => '<p>Ubiquiti airMAX products support outdoor wireless links for ISPs, installers and remote site connectivity.</p>',
                'seo_content' => '<h2>Ubiquiti airMAX equipment for Kenyan networks</h2><p>Use airMAX categories to compare radios, antennas and outdoor wireless equipment by link type, distance target and installation environment. Verify local spectrum planning and line-of-sight before deployment.</p>',
                'faq_items' => [
                    ['question' => 'Is Ubiquiti airMAX used for ISP networks?', 'answer' => 'Yes, airMAX equipment is commonly used for outdoor wireless ISP and point-to-point deployments where the model suits the link design.'],
                ],
            ],
            'ubiquiti-point-to-point' => [
                'name' => 'Ubiquiti Point-to-Point Wireless',
                'h1' => 'Ubiquiti Point-to-Point Wireless in Kenya',
                'focus_keyword' => 'Ubiquiti Point to Point Kenya',
                'meta_description' => 'Compare Ubiquiti point-to-point wireless equipment in Kenya for building links, ISP backhaul and remote connectivity.',
                'intro' => 'Find Ubiquiti radios for point-to-point wireless links across campuses, branches, farms and ISP networks.',
                'description' => '<p>Point-to-point wireless links connect two locations using outdoor radios and clear line-of-sight planning.</p>',
                'seo_content' => '<h2>Planning a Ubiquiti point-to-point link</h2><p>Before buying equipment, confirm distance, clear line of sight, mounting height, power availability, interference and throughput requirements.</p>',
                'faq_items' => [
                    ['question' => 'What should I check before installing a point-to-point link?', 'answer' => 'Confirm line of sight, distance, mounting positions, power, grounding and expected throughput before choosing equipment.'],
                ],
            ],
            'ubiquiti-airfiber' => [
                'name' => 'Ubiquiti airFiber',
                'h1' => 'Ubiquiti airFiber in Kenya',
                'focus_keyword' => 'Ubiquiti airFiber Kenya',
                'meta_description' => 'Shop Ubiquiti airFiber equipment in Kenya for high-capacity wireless backhaul and ISP links.',
                'intro' => 'Compare Ubiquiti airFiber options for high-capacity outdoor wireless backhaul.',
                'description' => '<p>Ubiquiti airFiber equipment is used for higher-capacity wireless links where the network design requires it.</p>',
                'seo_content' => '<h2>Ubiquiti airFiber buying notes</h2><p>Confirm frequency planning, distance, antennas, mounting, grounding and throughput targets before selecting airFiber hardware.</p>',
                'faq_items' => [],
            ],
            'ubiquiti-uisp' => [
                'name' => 'Ubiquiti UISP',
                'h1' => 'Ubiquiti UISP in Kenya',
                'focus_keyword' => 'Ubiquiti UISP Kenya',
                'meta_description' => 'Browse Ubiquiti UISP routers, switches, wireless, fiber and power products in Kenya for ISP networks.',
                'intro' => 'Find UISP equipment for service-provider routing, switching, wireless, fiber and power deployments.',
                'description' => '<p>Ubiquiti UISP products support ISP-oriented network deployments and management workflows.</p>',
                'seo_content' => '<h2>Ubiquiti UISP for service providers</h2><p>Use UISP categories to organize ISP routers, switches, wireless equipment, fiber equipment and power accessories for easier procurement.</p>',
                'faq_items' => [],
            ],
            'ubiquiti-cameras' => [
                'name' => 'Ubiquiti Cameras',
                'h1' => 'Ubiquiti Cameras in Kenya',
                'focus_keyword' => 'Ubiquiti Cameras Kenya',
                'meta_description' => 'Shop Ubiquiti UniFi Protect cameras in Kenya including indoor, outdoor, bullet, dome, turret, PTZ, AI and doorbell cameras.',
                'intro' => 'Compare UniFi Protect cameras for homes, offices, warehouses, schools, hotels and commercial security projects.',
                'description' => '<p>Ubiquiti cameras are used with UniFi Protect deployments for video surveillance and security monitoring.</p>',
                'seo_content' => '<h2>Choosing Ubiquiti cameras in Kenya</h2><p>Choose cameras by indoor or outdoor use, mounting style, lens requirements, AI features, night coverage and recording needs. Confirm compatibility with your UniFi Protect recorder.</p>',
                'faq_items' => [
                    ['question' => 'Do Ubiquiti cameras need UniFi Protect?', 'answer' => 'UniFi cameras are designed for UniFi Protect deployments. Confirm recorder and application compatibility before purchase.'],
                ],
            ],
            'ubiquiti-nvr' => [
                'name' => 'Ubiquiti NVR',
                'h1' => 'Ubiquiti NVR in Kenya',
                'focus_keyword' => 'Ubiquiti NVR Kenya',
                'meta_description' => 'Compare Ubiquiti NVR and UniFi Protect recording options in Kenya for surveillance projects.',
                'intro' => 'Choose Ubiquiti NVR equipment for UniFi Protect recording and video storage.',
                'description' => '<p>Ubiquiti NVR products support UniFi Protect camera recording and storage.</p>',
                'seo_content' => '<h2>Ubiquiti NVR planning</h2><p>Match NVR storage and camera capacity to your surveillance design. Verify drive, camera and application support before publishing exact specifications.</p>',
                'faq_items' => [],
            ],
            'ubiquiti-access-control' => [
                'name' => 'Ubiquiti Access Control',
                'h1' => 'Ubiquiti Access Control in Kenya',
                'focus_keyword' => 'Ubiquiti Access Control Kenya',
                'meta_description' => 'Shop Ubiquiti UniFi Access products in Kenya including readers, door hubs, access kits, intercoms, cards, keyfobs and accessories.',
                'intro' => 'Compare UniFi Access readers, hubs, cards, intercoms and accessories for door access control installations.',
                'description' => '<p>Ubiquiti access control products support managed door entry and intercom installations.</p>',
                'seo_content' => '<h2>Ubiquiti access control buying notes</h2><p>Plan readers, hubs, credentials, door hardware, cabling, power and installation requirements before selecting UniFi Access equipment.</p>',
                'faq_items' => [
                    ['question' => 'What is needed for UniFi Access control?', 'answer' => 'A complete installation may require readers, hubs, credentials, compatible locks, cabling and setup. Confirm the design before purchase.'],
                ],
            ],
            'ubiquiti-cloud-key' => [
                'name' => 'Ubiquiti Cloud Keys',
                'h1' => 'Ubiquiti Cloud Keys in Kenya',
                'focus_keyword' => 'Ubiquiti Cloud Key Kenya',
                'meta_description' => 'Compare Ubiquiti Cloud Key options in Kenya for UniFi network management and compatible applications.',
                'intro' => 'Find Ubiquiti Cloud Key devices for UniFi management where a dedicated appliance is preferred.',
                'description' => '<p>Ubiquiti Cloud Keys provide UniFi management for compatible network deployments.</p>',
                'seo_content' => '<h2>Ubiquiti Cloud Key selection</h2><p>Confirm application support, storage needs and managed device capacity before choosing a Cloud Key.</p>',
                'faq_items' => [],
            ],
            'ubiquiti-poe-injectors' => [
                'name' => 'Ubiquiti PoE Injectors',
                'h1' => 'Ubiquiti PoE Injectors in Kenya',
                'focus_keyword' => 'Ubiquiti PoE Injectors Kenya',
                'meta_description' => 'Shop Ubiquiti PoE injectors and adapters in Kenya for powering compatible access points, radios and network devices.',
                'intro' => 'Find PoE injectors and adapters for compatible Ubiquiti devices when a PoE switch is not used.',
                'description' => '<p>PoE injectors power compatible Ubiquiti network devices over Ethernet.</p>',
                'seo_content' => '<h2>Choosing a Ubiquiti PoE injector</h2><p>Check voltage, wattage, PoE standard and device compatibility before buying a PoE injector.</p>',
                'faq_items' => [],
            ],
            'ubiquiti-antennas' => [
                'name' => 'Ubiquiti Antennas',
                'h1' => 'Ubiquiti Antennas in Kenya',
                'focus_keyword' => 'Ubiquiti Antennas Kenya',
                'meta_description' => 'Shop Ubiquiti antennas in Kenya including sector, omni, dish, airMAX and airFiber antenna options.',
                'intro' => 'Compare Ubiquiti antennas for outdoor wireless links, sectors, omni coverage and backhaul planning.',
                'description' => '<p>Ubiquiti antennas support outdoor wireless link and coverage designs.</p>',
                'seo_content' => '<h2>Ubiquiti antenna selection</h2><p>Match antenna type, gain, beamwidth, frequency band and mounting requirements to the wireless link design.</p>',
                'faq_items' => [],
            ],
            'ubiquiti-accessories' => [
                'name' => 'Ubiquiti Accessories',
                'h1' => 'Ubiquiti Accessories in Kenya',
                'focus_keyword' => 'Ubiquiti Accessories Kenya',
                'meta_description' => 'Shop Ubiquiti accessories in Kenya including PoE adapters, mounts, rack accessories, SFP modules, DAC cables, surge protectors and power supplies.',
                'intro' => 'Find Ubiquiti accessories for powering, mounting, cabling, protecting and expanding network deployments.',
                'description' => '<p>Ubiquiti accessories support installation, power, fiber, rack and cabling needs.</p>',
                'seo_content' => '<h2>Ubiquiti accessories for installations</h2><p>Use accessories to complete network deployments with correct power, mounting, fiber, surge protection and cabling components.</p>',
                'faq_items' => [],
            ],
            'ubiquiti-network-accessories' => [
                'name' => 'Ubiquiti Network Accessories',
                'h1' => 'Ubiquiti Network Accessories in Kenya',
                'focus_keyword' => 'Ubiquiti Network Accessories Kenya',
                'meta_description' => 'Shop Ubiquiti network accessories in Kenya including PoE injectors, SFP modules, DAC cables, surge protectors, mounts and power supplies.',
                'intro' => 'Find Ubiquiti network accessories for powering, mounting, cabling, protecting and expanding network deployments.',
                'description' => '<p>Ubiquiti network accessories support PoE, fiber, rack, mounting and cabling needs.</p>',
                'seo_content' => '<h2>Ubiquiti network accessories for installations</h2><p>Use network accessories to complete deployments with correct power, mounting, fiber, surge protection and cabling components.</p>',
                'faq_items' => [],
            ],
            'ubiquiti-fiber' => [
                'name' => 'Ubiquiti Fiber',
                'h1' => 'Ubiquiti Fiber in Kenya',
                'focus_keyword' => 'Ubiquiti Fiber Kenya',
                'meta_description' => 'Shop Ubiquiti fiber products in Kenya including UFiber OLT, UFiber ONU, SFP, SFP+, DAC cables and fiber accessories.',
                'intro' => 'Compare Ubiquiti fiber products and modules for ISP, business and backbone network deployments.',
                'description' => '<p>Ubiquiti fiber products support optical networking, uplinks and service-provider deployments.</p>',
                'seo_content' => '<h2>Ubiquiti fiber buying notes</h2><p>Confirm optical standard, connector type, speed, distance and device compatibility before selecting fiber modules or UFiber equipment.</p>',
                'faq_items' => [],
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function subcategories(): array
    {
        return [
            'ubiquiti-access-points' => [
                'WiFi 7 Access Points',
                'WiFi 6 Access Points',
                'Indoor Access Points',
                'Outdoor Access Points',
                'Long Range Access Points',
                'Mesh Access Points',
                'In-Wall Access Points',
                'Enterprise Access Points',
            ],
            'ubiquiti-switches' => [
                'PoE Switches',
                'Non-PoE Switches',
                '8-Port Switches',
                '16-Port Switches',
                '24-Port Switches',
                '48-Port Switches',
                '2.5G Switches',
                '10G Switches',
                'Enterprise Switches',
            ],
            'ubiquiti-routers' => [
                'UniFi Routers',
                'ISP Routers',
                'Multi-WAN Routers',
                'VPN Routers',
                'EdgeRouters',
            ],
            'ubiquiti-airmax' => [
                'LiteBeam',
                'NanoBeam',
                'NanoStation',
                'PowerBeam',
                'Rocket',
                'Rocket Prism',
                'airMAX Sector Antennas',
                'airMAX Omni Antennas',
            ],
            'ubiquiti-uisp' => [
                'UISP Routers',
                'UISP Switches',
                'UISP Wireless',
                'UISP Fiber',
                'UISP Power',
            ],
            'ubiquiti-cameras' => [
                'Indoor Cameras',
                'Outdoor Cameras',
                'Bullet Cameras',
                'Dome Cameras',
                'Turret Cameras',
                'PTZ Cameras',
                'AI Cameras',
                'Doorbell Cameras',
            ],
            'ubiquiti-access-control' => [
                'Access Readers',
                'Door Hubs',
                'Access Kits',
                'Intercoms',
                'Access Cards',
                'Keyfobs',
                'Access Control Accessories',
            ],
            'ubiquiti-antennas' => [
                'Sector Antennas',
                'Omni Antennas',
                'Dish Antennas',
                'airMAX Antennas',
                'airFiber Antennas',
            ],
            'ubiquiti-accessories' => [
                'PoE Adapters',
                'Mounting Brackets',
                'Rack Accessories',
                'SFP Modules',
                'DAC Cables',
                'Fiber Modules',
                'Surge Protectors',
                'Power Supplies',
                'Network Cables',
            ],
            'ubiquiti-fiber' => [
                'UFiber OLT',
                'UFiber ONU',
                'SFP',
                'SFP+',
                'Fiber Accessories',
                'DAC Cables for Fiber',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categoryTitles(): array
    {
        return collect(self::primaryCategories())
            ->mapWithKeys(fn (array $category, string $slug): array => [$slug => $category['h1'].' | Ubiquiti UniFi Kenya'])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function comparisonPages(): array
    {
        return [
            'u6-plus-vs-u6-pro' => 'U6+ vs U6 Pro',
            'u6-pro-vs-u6-lr' => 'U6 Pro vs U6 Long Range',
            'u7-pro-vs-u6-pro' => 'U7 Pro vs U6 Pro',
            'u7-pro-vs-u7-pro-max' => 'U7 Pro vs U7 Pro Max',
            'cloud-gateway-ultra-vs-cloud-gateway-max' => 'Cloud Gateway Ultra vs Cloud Gateway Max',
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function comparisonProducts(): array
    {
        return [
            'u6-plus-vs-u6-pro' => ['U6+', 'U6 Pro'],
            'u6-pro-vs-u6-lr' => ['U6 Pro', 'U6 Long Range'],
            'u7-pro-vs-u6-pro' => ['U7 Pro', 'U6 Pro'],
            'u7-pro-vs-u7-pro-max' => ['U7 Pro', 'U7 Pro Max'],
            'cloud-gateway-ultra-vs-cloud-gateway-max' => ['Cloud Gateway Ultra', 'Cloud Gateway Max'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function resolvableComparisonSlugs(): array
    {
        $products = Product::query()->active()->get(['id', 'name', 'slug', 'sku']);
        $resolvable = [];

        foreach (self::comparisonProducts() as $slug => [$left, $right]) {
            if (self::findMatchingProduct($products, $left) && self::findMatchingProduct($products, $right)) {
                $resolvable[] = $slug;
            }
        }

        return $resolvable;
    }

    public static function navLabel(Category $category): string
    {
        $slug = Str::slug($category->slug);

        if ($mapped = self::primaryCategories()[$slug]['name'] ?? null) {
            return $mapped;
        }

        $name = trim((string) $category->name);
        $name = preg_replace('/\s*[-|:]\s*price(s)?\s*in\s*kenya\s*$/iu', '', $name) ?? $name;
        $name = preg_replace('/\s*price(s)?\s*in\s*kenya\s*$/iu', '', $name) ?? $name;
        $name = preg_replace('/\s*for\s*sale\s*in\s*kenya\s*$/iu', '', $name) ?? $name;

        return $name !== '' ? $name : $category->name;
    }

    /**
     * @return array<string, string>
     */
    public static function legacyCategoryRedirects(): array
    {
        return [
            'ubiquity-access-points' => 'ubiquiti-access-points',
            'ubiquity-switches' => 'ubiquiti-switches',
            'ubiquity-cloud-gateways' => 'ubiquiti-cloud-gateways',
            'ubiquity-routers' => 'ubiquiti-routers',
            'ubiquity-airmax' => 'ubiquiti-airmax',
            'ubiquity-point-to-point' => 'ubiquiti-point-to-point',
            'ubiquity-airfiber' => 'ubiquiti-airfiber',
            'ubiquity-uisp' => 'ubiquiti-uisp',
            'ubiquity-cameras' => 'ubiquiti-cameras',
            'ubiquity-nvr' => 'ubiquiti-nvr',
            'ubiquity-access-control' => 'ubiquiti-access-control',
            'ubiquity-cloud-key' => 'ubiquiti-cloud-key',
            'ubiquity-poe-injectors' => 'ubiquiti-poe-injectors',
            'ubiquity-antennas' => 'ubiquiti-antennas',
            'ubiquity-accessories' => 'ubiquiti-accessories',
            'ubiquity-fiber' => 'ubiquiti-fiber',
            'unifi-access-points' => 'ubiquiti-access-points',
            'unifi-switches' => 'ubiquiti-switches',
            'unifi-gateways' => 'ubiquiti-cloud-gateways',
            'unifi-routers' => 'ubiquiti-routers',
            'unifi-cameras' => 'ubiquiti-cameras',
            'unifi-protect' => 'ubiquiti-cameras',
            'unifi-nvr' => 'ubiquiti-nvr',
            'unifi-access-control' => 'ubiquiti-access-control',
            'unifi-cloud-key' => 'ubiquiti-cloud-key',
            'ubiquiti-products' => 'ubiquiti-access-points',
            'ubiquiti-products-in-kenya' => 'ubiquiti-access-points',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function topLevelCategoryRedirects(): array
    {
        return array_merge(
            array_combine(array_keys(self::primaryCategories()), array_keys(self::primaryCategories())),
            self::legacyCategoryRedirects()
        );
    }

    public static function isRouterAuthorityCategory(?Category $category): bool
    {
        if (! $category) {
            return false;
        }

        return Str::slug($category->slug) === self::ROUTER_AUTHORITY_SLUG;
    }

    public static function isBroadUbiquitiCategory(?Category $category): bool
    {
        if (! $category) {
            return false;
        }

        return in_array(Str::slug($category->slug), [
            'ubiquiti',
            'ubiquiti-kenya',
            'ubiquiti-products',
            'ubiquiti-products-in-kenya',
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
            $product->model_number,
            $product->category?->name,
            $product->category?->slug,
        ]));

        if (Str::contains($text, ['access point', 'u7 ', 'u7-', 'u6 ', 'u6-', 'uap', 'mesh', 'in-wall', 'in wall'])) {
            return 'ubiquiti-access-points';
        }

        if (Str::contains($text, ['camera', 'g5 ', 'g5-', 'g6 ', 'g6-', 'ai bullet', 'ai dome', 'ai theta', 'doorbell', 'protect'])) {
            return 'ubiquiti-cameras';
        }

        if (Str::contains($text, ['nvr', 'network video recorder'])) {
            return 'ubiquiti-nvr';
        }

        if (Str::contains($text, ['access reader', 'door hub', 'access hub', 'intercom', 'keyfob', 'keyfobs', 'access card', 'unifi access'])) {
            return 'ubiquiti-access-control';
        }

        if (Str::contains($text, ['cloud gateway', 'dream machine', 'unifi express', 'enterprise fortress gateway', 'udm'])) {
            return 'ubiquiti-cloud-gateways';
        }

        if (Str::contains($text, ['edgerouter', 'router', 'multi-wan', 'vpn'])) {
            return 'ubiquiti-routers';
        }

        if (Str::contains($text, ['airfiber', 'air fiber'])) {
            return 'ubiquiti-airfiber';
        }

        if (Str::contains($text, ['uisp'])) {
            return 'ubiquiti-uisp';
        }

        if (Str::contains($text, ['litebeam', 'nanobeam', 'nanostation', 'powerbeam', 'rocket prism', 'rocket ', 'airmax'])) {
            return 'ubiquiti-airmax';
        }

        if (Str::contains($text, ['point-to-point', 'point to point', 'ptp'])) {
            return 'ubiquiti-point-to-point';
        }

        if (Str::contains($text, ['switch', 'flex mini', 'flex 2.5g', 'lite 8', 'lite 16', 'enterprise xg'])) {
            return 'ubiquiti-switches';
        }

        if (Str::contains($text, ['poe injector', 'poe adapter'])) {
            return 'ubiquiti-poe-injectors';
        }

        if (Str::contains($text, ['antenna', 'sector', 'omni', 'dish'])) {
            return 'ubiquiti-antennas';
        }

        if (Str::contains($text, ['ufiber', 'fiber', 'fibre', 'sfp', 'sfp+', 'dac'])) {
            return 'ubiquiti-fiber';
        }

        if (Str::contains($text, ['ubiquiti', 'ubiquity', 'unifi', 'airmax', 'airfiber'])) {
            return 'ubiquiti-accessories';
        }

        return null;
    }

    public static function ubiquitiProductsQuery(): Builder
    {
        return Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active()
            ->where(function (Builder $query): void {
                $query->where('name', 'like', '%Ubiquiti%')
                    ->orWhere('name', 'like', '%Ubiquity%')
                    ->orWhere('name', 'like', '%UniFi%')
                    ->orWhere('name', 'like', '%airMAX%')
                    ->orWhere('name', 'like', '%UISP%')
                    ->orWhere('slug', 'like', '%ubiquiti%')
                    ->orWhere('slug', 'like', '%ubiquity%')
                    ->orWhere('slug', 'like', '%unifi%')
                    ->orWhere('description', 'like', '%Ubiquiti%')
                    ->orWhere('description', 'like', '%UniFi%')
                    ->orWhereHas('category', function (Builder $categoryQuery): void {
                        $categoryQuery->where('name', 'like', '%Ubiquiti%')
                            ->orWhere('name', 'like', '%Ubiquity%')
                            ->orWhere('name', 'like', '%UniFi%')
                            ->orWhere('slug', 'like', '%ubiquiti%')
                            ->orWhere('slug', 'like', '%unifi%');
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
                'question' => 'Which Ubiquiti router is best for a small business in Kenya?',
                'answer' => 'Choose based on internet speed, user count, VPN needs, security features, UniFi management requirements and the number of WAN/LAN ports required.',
            ],
            [
                'question' => 'Are Ubiquiti router prices updated automatically?',
                'answer' => 'Prices on this page come from product records and update when the catalogue price is updated in the admin area.',
            ],
            [
                'question' => 'Should I choose a Cloud Gateway or a separate Ubiquiti router?',
                'answer' => 'Choose a Cloud Gateway when you want UniFi management and routing in one device. Choose a separate router when the network design requires a different routing platform or ISP-specific setup.',
            ],
        ];
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
}
