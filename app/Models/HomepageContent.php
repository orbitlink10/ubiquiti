<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HomepageContent extends Model
{
    use HasFactory;

    public const DEFAULT_SITE_KEY = 'default';

    protected $fillable = [
        'site_key',
        'site_logo_path',
        'contact_phone',
        'contact_whatsapp',
        'contact_email',
        'hero_title',
        'hero_description',
        'hero_image_path',
        'why_choose_title',
        'why_choose_intro',
        'why_choose_items',
        'testimonials_badge',
        'testimonials_title',
        'testimonials_intro',
        'testimonial_items',
        'faq_badge',
        'faq_title',
        'faq_intro',
        'faq_items',
        'content_badge',
        'content_title',
        'content_intro',
        'content_body',
        'featured_product_ids',
        'nav_menu_items',
    ];

    protected $casts = [
        'why_choose_items' => 'array',
        'testimonial_items' => 'array',
        'faq_items' => 'array',
        'featured_product_ids' => 'array',
        'nav_menu_items' => 'array',
    ];

    public static function current(): self
    {
        if (! static::storageReady()) {
            return static::defaultContent();
        }

        return static::query()->where('site_key', static::DEFAULT_SITE_KEY)->first()
            ?? static::defaultContent();
    }

    public static function storageReady(): bool
    {
        $table = (new static)->getTable();

        if (! Schema::hasTable($table)) {
            return false;
        }

        foreach ([
            'site_key',
            'site_logo_path',
            'contact_phone',
            'contact_whatsapp',
            'contact_email',
            'hero_title',
            'hero_description',
            'hero_image_path',
            'why_choose_title',
            'why_choose_intro',
            'why_choose_items',
            'testimonials_badge',
            'testimonials_title',
            'testimonials_intro',
            'testimonial_items',
            'faq_badge',
            'faq_title',
            'faq_intro',
            'faq_items',
            'content_badge',
            'content_title',
            'content_intro',
            'content_body',
            'featured_product_ids',
            'nav_menu_items',
        ] as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private static function defaultContent(): self
    {
        return new static([
            'site_key' => static::DEFAULT_SITE_KEY,
            'contact_phone' => config('business.phone'),
            'contact_whatsapp' => config('business.whatsapp', config('business.phone')),
            'contact_email' => config('business.email'),
            'hero_title' => 'Ubiquiti UniFi Kenya - Access Points, Switches & Gateways',
            'hero_description' => 'Compare genuine Ubiquiti UniFi access points, switches, gateways, cameras and wireless systems with current prices, specifications, stock availability and fast delivery across Kenya.',
            'why_choose_title' => 'Why Buy Ubiquiti UniFi Equipment From Us?',
            'why_choose_intro' => 'Compare practical UniFi, UISP, airMAX and airFiber hardware for homes, offices, ISPs and enterprise networks from a catalogue built around Kenyan networking needs.',
            'why_choose_items' => self::defaultWhyChooseItems(),
            'testimonials_badge' => 'Testimonials',
            'testimonials_title' => 'Customer Feedback',
            'testimonials_intro' => 'Customer experiences with our networking products and support.',
            'testimonial_items' => self::defaultTestimonialItems(),
            'faq_badge' => 'FAQ',
            'faq_title' => 'Ubiquiti UniFi Buying Questions',
            'faq_intro' => 'Answers to common questions about Ubiquiti UniFi prices, stock, delivery and product selection in Kenya.',
            'faq_items' => self::defaultFaqItems(),
            'content_badge' => 'Ubiquiti UniFi Kenya Guide',
            'content_title' => 'Ubiquiti UniFi Kenya: Networking Hardware for Homes, Offices and ISPs',
            'content_intro' => 'Explore Ubiquiti UniFi products for WiFi, switching, gateways, cameras, wireless links and network management.',
            'content_body' => self::defaultContentBody(),
            'nav_menu_items' => [],
        ]);
    }

    public function heroImageUrl(): ?string
    {
        return $this->existingAssetUrl($this->hero_image_path);
    }

    public function siteLogoUrl(): ?string
    {
        $url = $this->existingAssetUrl($this->site_logo_path);

        if ($url) {
            return $url;
        }

        return $this->existingAssetUrl($this->findExistingLogoPath());
    }

    /**
     * Resolve a stored asset path to a public URL only when the file
     * actually exists, so broken paths never render broken images.
     */
    private function existingAssetUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        $publicFile = public_path($path);
        if (is_file($publicFile)) {
            return asset($path);
        }

        if (str_starts_with($path, 'storage/')) {
            $storageFile = storage_path('app/public/'.substr($path, 8));
            if (is_file($storageFile)) {
                return asset($path);
            }
        }

        return null;
    }

    private function findExistingLogoPath(): ?string
    {
        $directory = public_path('uploads/homepage-content');

        if (! is_dir($directory)) {
            return null;
        }

        $files = glob($directory.DIRECTORY_SEPARATOR.'*-logo-*') ?: [];
        sort($files);

        foreach ($files as $file) {
            if (is_file($file)) {
                return 'uploads/homepage-content/'.basename($file);
            }
        }

        return null;
    }

    public function contactPhone(): ?string
    {
        return $this->fallbackNullableText(config('business.phone'), $this->contact_phone);
    }

    public function contactWhatsApp(): ?string
    {
        return $this->fallbackNullableText($this->contact_whatsapp, config('business.whatsapp', config('business.phone')));
    }

    public function contactEmail(): ?string
    {
        return $this->fallbackNullableText(config('business.email'), $this->contact_email);
    }

    public function whyChooseTitle(): string
    {
        return $this->fallbackText($this->why_choose_title, static::defaultContent()->why_choose_title);
    }

    public function whyChooseIntro(): ?string
    {
        return $this->fallbackNullableText($this->why_choose_intro, static::defaultContent()->why_choose_intro);
    }

    public function whyChooseItems(): array
    {
        return $this->normalizeItems($this->why_choose_items, ['title', 'description'], self::defaultWhyChooseItems());
    }

    public function testimonialsBadge(): ?string
    {
        return $this->fallbackNullableText($this->testimonials_badge, static::defaultContent()->testimonials_badge);
    }

    public function testimonialsTitle(): string
    {
        return $this->fallbackText($this->testimonials_title, static::defaultContent()->testimonials_title);
    }

    public function testimonialsIntro(): ?string
    {
        if ($this->testimonials_intro === 'Feedback from customers can be managed from the admin panel when genuine testimonials are available.') {
            return static::defaultContent()->testimonials_intro;
        }

        return $this->fallbackNullableText($this->testimonials_intro, static::defaultContent()->testimonials_intro);
    }

    public function testimonialItems(): array
    {
        return $this->normalizeItems($this->testimonial_items, ['quote', 'name', 'role'], self::defaultTestimonialItems());
    }

    public function faqBadge(): ?string
    {
        return $this->fallbackNullableText($this->faq_badge, static::defaultContent()->faq_badge);
    }

    public function faqTitle(): string
    {
        return $this->fallbackText($this->faq_title, static::defaultContent()->faq_title);
    }

    public function faqIntro(): ?string
    {
        return $this->fallbackNullableText($this->faq_intro, static::defaultContent()->faq_intro);
    }

    public function faqItems(): array
    {
        return $this->normalizeItems($this->faq_items, ['question', 'answer'], self::defaultFaqItems());
    }

    public function contentBadge(): ?string
    {
        return $this->fallbackNullableText($this->content_badge, static::defaultContent()->content_badge);
    }

    public function contentTitle(): string
    {
        return $this->fallbackText($this->content_title, static::defaultContent()->content_title);
    }

    public function contentIntro(): ?string
    {
        return $this->fallbackNullableText($this->content_intro, static::defaultContent()->content_intro);
    }

    public function contentBody(): string
    {
        $html = trim((string) $this->content_body);

        return \App\Support\CatalogContent::render(
            $html !== '' ? $html : (string) static::defaultContent()->content_body
        ) ?? '';
    }

    /**
     * @return array<int>
     */
    public function featuredProductIds(): array
    {
        if (! is_array($this->featured_product_ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $this->featured_product_ids),
            fn (int $id): bool => $id > 0
        )));
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public function navMenuItems(): array
    {
        return self::normalizeNavMenuItems($this->nav_menu_items);
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public static function normalizeNavMenuItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = Str::limit(static::plainText($item['label'] ?? ''), 80, '');
            $url = static::cleanMenuUrl($item['url'] ?? '');

            if ($label === '' || $url === null) {
                continue;
            }

            $normalized[] = [
                'label' => $label,
                'url' => $url,
            ];

            if (count($normalized) >= 8) {
                break;
            }
        }

        return $normalized;
    }

    private static function cleanMenuUrl(mixed $value): ?string
    {
        $url = trim((string) $value);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '#')) {
            return Str::limit($url, 255, '');
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return Str::limit($url, 255, '');
        }

        if (! preg_match('/^[a-z][a-z0-9+.-]*:/i', $url) && ! preg_match('/\s/', $url)) {
            return '/'.ltrim(Str::limit($url, 254, ''), '/');
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $scheme = parse_url($url, PHP_URL_SCHEME);

            if (in_array($scheme, ['http', 'https'], true)) {
                return Str::limit($url, 255, '');
            }
        }

        return null;
    }

    private function fallbackText(mixed $value, string $default): string
    {
        $text = $this->cleanText($value);

        return $text !== null ? $text : $default;
    }

    private function fallbackNullableText(mixed $value, ?string $default): ?string
    {
        $text = $this->cleanText($value);

        return $text !== null ? $text : $default;
    }

    private function cleanText(mixed $value): ?string
    {
        $text = static::plainText($value);

        return $text !== '' ? $text : null;
    }

    private static function plainText(mixed $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)) ?? '');
    }

    /**
     * @param  array<int, string>  $keys
     * @param  array<int, array<string, string>>  $defaults
     * @return array<int, array<string, string>>
     */
    private function normalizeItems(mixed $items, array $keys, array $defaults): array
    {
        if (! is_array($items)) {
            return $defaults;
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = [];
            foreach ($keys as $key) {
                $text = $this->cleanText($item[$key] ?? null);
                if ($text === null) {
                    continue 2;
                }

                $row[$key] = Str::limit($text, $key === 'quote' || $key === 'answer' ? 1200 : 220, '');
            }

            $normalized[] = $row;
        }

        return $normalized !== [] ? $normalized : $defaults;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function defaultWhyChooseItems(): array
    {
        return [
            ['title' => 'Current Catalogue Prices', 'description' => 'Compare current prices and availability on each product page.'],
            ['title' => 'UniFi-Focused Selection', 'description' => 'Browse access points, switches, gateways, cameras, wireless systems and accessories by practical network use.'],
            ['title' => 'Product-Level Details', 'description' => 'Review SKU, stock status, category, use cases and technical guidance before purchase.'],
            ['title' => 'Quotation Friendly', 'description' => 'Business buyers can use product pages as a starting point for larger networking enquiries.'],
            ['title' => 'Delivery Information', 'description' => 'Delivery options are confirmed during checkout or enquiry based on product availability and destination.'],
            ['title' => 'Configuration Guidance', 'description' => 'Product pages include UniFi, UISP and compatibility notes where available.'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function defaultTestimonialItems(): array
    {
        return [
            [
                'quote' => 'Add genuine customer feedback from completed orders or verified support interactions in the admin panel.',
                'name' => 'Customer feedback',
                'role' => 'Managed from admin',
            ],
            [
                'quote' => 'Do not publish ratings or reviews unless they come from real customers and can be supported by business records.',
                'name' => 'Review policy',
                'role' => 'Verified reviews only',
            ],
            [
                'quote' => 'Use this section for real installation, procurement or support feedback once available.',
                'name' => 'Trust signals',
                'role' => 'Real customer proof',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function defaultFaqItems(): array
    {
        return [
            [
                'question' => 'Are prices on the website current?',
                'answer' => 'Product pages display current catalogue prices. Confirm pricing and stock when requesting a quotation.',
            ],
            [
                'question' => 'Can I compare Ubiquiti UniFi products before buying?',
                'answer' => 'Use category pages and product pages to compare price, stock status, SKU, category and recommended applications.',
            ],
            [
                'question' => 'Do product pages show stock status?',
                'answer' => 'Yes. Each product page shows whether the product is currently listed as available or out of stock.',
            ],
            [
                'question' => 'Can businesses request quotations?',
                'answer' => 'Contact us with your required models and quantities to request a business quotation.',
            ],
        ];
    }

    private static function defaultContentBody(): string
    {
        return implode('', [
            '<h2>Ubiquiti UniFi networking equipment in Kenya</h2>',
            '<p>Ubiquiti UniFi access points, switches, gateways, cameras and wireless systems are used for home internet, office networks, ISP deployments, CCTV, VPNs and fibre uplinks.</p>',
            '<p>Use the catalogue to compare current prices, stock status, SKUs and product categories before choosing a UniFi, UISP, airMAX or airFiber device.</p>',
            '<h3>Where Ubiquiti UniFi fits best</h3>',
            '<ul>',
            '<li>Homes and small offices that need reliable routing and Wi-Fi management.</li>',
            '<li>Businesses that need firewall, VPN, VLAN, PoE switching and multi-WAN UniFi features.</li>',
            '<li>ISPs and installers building outdoor wireless, fibre and aggregation networks.</li>',
            '</ul>',
            '<h3>What to Consider Before Buying</h3>',
            '<p>Check the number of Ethernet ports, throughput requirements, PoE support, SFP or SFP+ uplinks, wireless bands, camera needs, mounting options and UniFi management requirements.</p>',
            '<p>For larger networks, confirm compatibility with your switches, access points, power setup and internet handoff before purchase.</p>',
        ]);
    }
}
