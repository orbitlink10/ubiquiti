<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use App\Support\MikrotikSeoCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeoEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.canonical_url' => 'https://mikrotikkenya.co.ke']);
    }

    public function test_product_page_outputs_dynamic_metadata_and_schema(): void
    {
        $product = $this->createProduct([
            'name' => 'MikroTik RB5009UPr+S+IN',
            'slug' => 'mikrotik-rb5009upr-s-in',
            'sku' => 'RB5009UPr+S+IN',
            'model_number' => 'RB5009UPr+S+IN',
            'price' => '56000.00',
            'faq_items' => [
                ['question' => 'Does RB5009 support PoE?', 'answer' => 'Confirm PoE input and output requirements against the specific RB5009 variant before purchase.'],
            ],
        ]);

        $response = $this->get('/product/'.$product->slug);

        $response->assertOk();
        $response->assertSee('<title>RB5009UPr+S+IN Price in Kenya | MikroTik Router</title>', false);
        $response->assertSee('"@type":"Product"', false);
        $response->assertSee('"priceCurrency":"KES"', false);
        $response->assertSee('"price":"56000.00"', false);
        $response->assertSee('"availability":"https://schema.org/InStock"', false);
        $response->assertSee('"@type":"BreadcrumbList"', false);
        $response->assertSee('"@type":"FAQPage"', false);
        $response->assertSee('Who is this product best for?');
        $response->assertSee('Does RB5009 support PoE?');
    }

    public function test_router_authority_category_has_dynamic_price_table_and_faq_schema(): void
    {
        $category = Category::create([
            'name' => 'MikroTik Router Prices in Kenya',
            'slug' => MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG,
            'meta_description' => 'Router price guide.',
            'description' => '<p>Router buying guide.</p>',
        ]);

        $product = $this->createProduct([
            'category_id' => $category->id,
            'name' => 'MikroTik RB4011iGS+RM',
            'slug' => 'mikrotik-rb4011igs-rm',
            'sku' => 'RB4011iGS+RM',
            'price' => '29000.00',
        ]);

        $response = $this->get('/category/'.MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG);

        $response->assertOk();
        $response->assertSee('MikroTik router price list in Kenya');
        $response->assertSee(route('product.show', $product), false);
        $response->assertSee('KSh 29,000.00');
        $response->assertSee('How to choose a MikroTik router');
        $response->assertSee('"@type":"FAQPage"', false);
    }

    public function test_legacy_router_category_redirects_to_primary_router_page(): void
    {
        Category::create([
            'name' => 'MikroTik Router Prices in Kenya',
            'slug' => MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG,
        ]);

        Category::create([
            'name' => 'Mikrotik wired routers price in Kenya',
            'slug' => 'mikrotik-wired-routers-price-in-kenya',
        ]);

        $this->get('/category/mikrotik-wired-routers-price-in-kenya')
            ->assertRedirect('/category/'.MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG);
    }

    public function test_broad_mikrotik_category_uses_relevant_catalog_fallback(): void
    {
        $broadCategory = Category::create([
            'name' => 'Mikrotik',
            'slug' => 'mikrotik',
            'description' => '<p>All MikroTik product groups.</p>',
        ]);

        $routerCategory = Category::create([
            'name' => 'MikroTik Router Prices in Kenya',
            'slug' => MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG,
        ]);

        $product = $this->createProduct([
            'category_id' => $routerCategory->id,
            'name' => 'MikroTik hEX S',
            'slug' => 'mikrotik-hex-s',
            'sku' => 'RB760iGS',
        ]);

        $response = $this->get('/category/'.$broadCategory->slug);

        $response->assertOk();
        $response->assertSee('Showing relevant MikroTik products from the wider catalogue');
        $response->assertSee($product->name);
        $response->assertDontSee('No products found.');
    }

    public function test_sitemap_includes_canonical_public_urls_and_excludes_private_paths(): void
    {
        $category = Category::create([
            'name' => 'MikroTik Switches',
            'slug' => 'mikrotik-switches',
        ]);

        $product = $this->createProduct([
            'category_id' => $category->id,
            'name' => 'MikroTik CRS326-24G-2S+RM',
            'slug' => 'mikrotik-crs326-24g-2s-rm',
            'sku' => 'CRS326-24G-2S+RM',
        ]);

        Page::create([
            'meta_title' => 'RouterOS Guide',
            'meta_description' => 'RouterOS guide for buyers.',
            'title' => 'RouterOS Guide',
            'heading_two' => 'RouterOS',
            'slug' => 'routeros-guide',
            'type' => 'post',
            'body' => '<p>RouterOS content.</p>',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('https://mikrotikkenya.co.ke/category/mikrotik-switches', false);
        $response->assertSee('https://mikrotikkenya.co.ke/product/'.$product->slug, false);
        $response->assertSee('https://mikrotikkenya.co.ke/blog/routeros-guide', false);
        $response->assertDontSee('/admin', false);
        $response->assertDontSee('/checkout', false);
    }

    public function test_trust_page_fallback_exists_without_inventing_contact_details(): void
    {
        $this->get('/pages/contact-us')
            ->assertRedirect('/contact-us');

        $response = $this->get('/contact-us');

        $response->assertOk();
        $response->assertSee('Contact Mikrotik Kenya');
        $response->assertSee('<link rel="canonical" href="https://mikrotikkenya.co.ke/contact-us">', false);
        $response->assertDontSee('Official'.' MikroTik'.' Store');
    }

    public function test_known_comparison_page_requires_both_products_and_links_to_them(): void
    {
        $category = Category::create([
            'name' => 'MikroTik Router Prices in Kenya',
            'slug' => MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG,
        ]);

        $rb4011 = $this->createProduct([
            'category_id' => $category->id,
            'name' => 'MikroTik RB4011iGS+RM',
            'slug' => 'mikrotik-rb4011igs-rm',
            'sku' => 'RB4011iGS+RM',
        ]);

        $rb5009 = $this->createProduct([
            'category_id' => $category->id,
            'name' => 'MikroTik RB5009UG+S+IN',
            'slug' => 'mikrotik-rb5009ug-s-in',
            'sku' => 'RB5009UG+S+IN',
        ]);

        $response = $this->get('/compare/rb4011-vs-rb5009');

        $response->assertOk();
        $response->assertSee('RB4011 vs RB5009');
        $response->assertSee(route('product.show', $rb4011), false);
        $response->assertSee(route('product.show', $rb5009), false);
    }

    public function test_seo_audit_command_is_safe_to_run(): void
    {
        $exitCode = Artisan::call('seo:audit');

        $this->assertSame(0, $exitCode);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes = []): Product
    {
        $category = Category::find($attributes['category_id'] ?? null)
            ?? Category::first()
            ?? Category::create([
                'name' => 'MikroTik Routers',
                'slug' => MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG,
            ]);

        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'status' => 'active',
            'phone' => '0712345678',
        ]);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Mikrotik Kenya Store '.$vendorUser->id,
            'slug' => 'mikrotik-kenya-store-'.$vendorUser->id,
            'description' => 'Network and routing equipment.',
            'phone' => '0712345678',
            'address' => 'Nairobi',
            'is_approved' => true,
        ]);

        $product = Product::create(array_merge([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'MikroTik Router',
            'slug' => 'mikrotik-router',
            'description' => '<p>Reliable routing hardware for Kenyan networks.</p>',
            'meta_description' => null,
            'price' => '20000.00',
            'stock' => 5,
            'sku' => 'ROUTER',
            'status' => 'active',
        ], $attributes, [
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => 'active',
        ]));

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'https://example.com/product.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        return $product;
    }
}
