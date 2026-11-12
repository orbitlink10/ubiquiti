<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomepageContent;
use App\Models\Page;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Vendor;
use App\Support\CanonicalUrl;
use App\Support\CatalogContent;
use App\Support\ProductContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoRepairTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_metadata_rejects_stale_localhost_configuration(): void
    {
        config(['app.canonical_url' => 'http://localhost']);

        foreach (['/', '/contact-us', '/about-us', '/?page=2'] as $path) {
            $response = $this->get('https://ubiquityunifikenya.co.ke'.$path)->assertOk();
            $response->assertSee('<link rel="canonical" href="https://ubiquityunifikenya.co.ke', false);
            $response->assertDontSee('http://localhost', false);
        }
    }

    public function test_alternate_hosts_and_http_redirect_with_path_and_query_intact(): void
    {
        foreach (['http://ubiquityunifikenya.co.ke', 'http://www.ubiquityunifikenya.co.ke', 'https://www.ubiquityunifikenya.co.ke'] as $base) {
            $this->get($base.'/category/access-points?page=2&sort=price_asc')
                ->assertStatus(301)
                ->assertRedirect('https://ubiquityunifikenya.co.ke/category/access-points?page=2&sort=price_asc');
        }
        $this->get('http://localhost/')->assertOk();
    }

    public function test_sitemap_is_valid_xml_and_only_contains_published_pages_at_their_public_routes(): void
    {
        config(['app.canonical_url' => CanonicalUrl::PRODUCTION_URL]);
        Page::create(['title' => 'Published guide', 'slug' => 'published-guide', 'type' => 'post', 'status' => 'published', 'body' => '<p>Guide</p>']);
        Page::create(['title' => 'Draft guide', 'slug' => 'draft-guide', 'type' => 'post', 'status' => 'draft', 'body' => '<p>Draft</p>']);

        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $response->assertSee(CanonicalUrl::PRODUCTION_URL.'/blog/published-guide', false);
        $response->assertDontSee('draft-guide')->assertDontSee('localhost');
        $urls = array_map(fn ($entry) => (string) $entry->loc, iterator_to_array($xml->url, false));
        $this->assertCount(count(array_unique($urls)), $urls);
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_contact_page_uses_contact_details_already_configured_in_the_header(): void
    {
        config(['business.phone' => null, 'business.email' => null]);
        $homepage = HomepageContent::current();
        $homepage->fill(['contact_phone' => '0714804532', 'contact_email' => 'sales@example.test'])->save();
        $response = $this->get('/contact-us')->assertOk();
        $response->assertSee('0714804532')->assertSee('sales@example.test');
        $response->assertSee('Ubiquiti UniFi Kenya')->assertDontSee('Mikrotik Kenya');
        $this->get('/about-us')->assertOk()->assertDontSee('admin content area');
    }

    public function test_demo_testimonials_are_hidden_and_real_feedback_is_retained(): void
    {
        Testimonial::create(['name' => 'Demo', 'quote' => Testimonial::sampleQuotes()[0], 'is_active' => true]);
        Testimonial::create(['name' => 'Actual buyer', 'quote' => 'The access point works well in our office.', 'is_active' => true]);
        $response = $this->get('/')->assertOk();
        $response->assertDontSee(Testimonial::sampleQuotes()[0]);
        $response->assertSee('The access point works well in our office.');
        $response->assertDontSee('managed from the admin panel');
        $this->assertTrue(Testimonial::defaultItems()->isEmpty());
    }

    public function test_verified_business_details_replace_outdated_homepage_contact_values(): void
    {
        $homepage = HomepageContent::current();
        $homepage->fill(['contact_phone' => '0714804532', 'contact_email' => 'old@example.test'])->save();
        $response = $this->get('/contact-us')->assertOk();
        $response->assertSee('+254729299439')->assertSee('info@ubiquityunifikenya.co.ke');
        $response->assertSee('Kimathi Street, Lyrics House')->assertSee('8 AM - 5 PM');
        $response->assertSee('Ubiquiti Unifi Kenya');
        $response->assertDontSee('old@example.test');
        $this->get('/returns-policy')->assertOk()->assertSee('Our return window is 7 days.');
    }

    public function test_empty_headings_are_removed_without_losing_real_headings_or_text(): void
    {
        $html = ProductContent::sanitizeRichText('<h2><br></h2><h3>&nbsp;</h3><h2>WiFi equipment</h2><p>Details</p>');
        $this->assertSame('<h2>WiFi equipment</h2><p>Details</p>', $html);
        $homepage = new HomepageContent(['content_body' => '<h2></h2><p>Catalogue</p>']);
        $this->assertSame('<p>Catalogue</p>', $homepage->contentBody());
    }

    public function test_inherited_referrals_are_removed_but_manufacturer_sources_are_preserved(): void
    {
        $html = CatalogContent::render('<p><a href="https://reisenseo.com/product/u7-lite">U7 Lite</a> <a href="https://techspecs.ui.com/unifi">Manufacturer specifications</a></p>');
        $this->assertStringNotContainsString('reisenseo.com', $html);
        $this->assertStringContainsString('U7 Lite', $html);
        $this->assertStringContainsString('https://techspecs.ui.com/unifi', $html);
    }

    public function test_local_asset_schema_urls_are_repaired_and_external_cdn_urls_preserved(): void
    {
        config(['app.canonical_url' => CanonicalUrl::PRODUCTION_URL]);
        $this->assertSame(CanonicalUrl::PRODUCTION_URL.'/uploads/image.jpg', CanonicalUrl::absoluteAsset('http://localhost/uploads/image.jpg'));
        $this->assertSame('https://cdn.ecomm.ui.com/image.png', CanonicalUrl::absoluteAsset('https://cdn.ecomm.ui.com/image.png'));
        $this->assertStringContainsString('Sitemap: '.CanonicalUrl::PRODUCTION_URL.'/sitemap.xml', file_get_contents(public_path('robots.txt')));
    }

    public function test_product_schema_and_inherited_referrals_use_public_internal_urls(): void
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $category = Category::create(['name' => 'Ubiquiti Access Points', 'slug' => 'ubiquiti-access-points']);
        $vendor = Vendor::create(['user_id' => $user->id, 'shop_name' => 'Ubiquiti store', 'slug' => 'ubiquiti-store', 'phone' => '0714804532', 'address' => 'Test address', 'is_approved' => true]);
        $product = Product::create(['vendor_id' => $vendor->id, 'category_id' => $category->id, 'name' => 'Ubiquiti U7 Lite', 'slug' => 'ubiquiti-u7-lite', 'sku' => 'U7-Lite', 'price' => 17500, 'stock' => 5, 'status' => 'active', 'description' => '<p>WiFi access point.</p>']);
        config(['app.canonical_url' => 'http://localhost']);
        $response = $this->get('https://ubiquityunifikenya.co.ke/product/'.$product->slug)->assertOk();
        $response->assertDontSee('http://localhost', false);
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $scripts);
        $schemas = array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $scripts[1]);
        $schema = collect($schemas)->firstWhere('@type', 'Product');
        $this->assertSame(CanonicalUrl::PRODUCTION_URL.'/product/'.$product->slug, $schema['offers']['url']);
        $this->assertSame('Ubiquiti UniFi Kenya', $schema['offers']['seller']['name']);
        $this->assertSame('17500.00', $schema['offers']['price']);
        $html = CatalogContent::render('<a href="https://orbitlinksolutions.co.ke/product/u7-lite">U7 Lite at Orbitlink</a>');
        $this->assertStringContainsString(CanonicalUrl::PRODUCTION_URL.'/product/'.$product->slug, $html);
        $this->assertStringNotContainsString('orbitlinksolutions', $html);
    }
}
