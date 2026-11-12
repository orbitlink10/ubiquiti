<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Support\MikrotikSeoCatalog;
use App\Support\ProductSeo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MikrotikMediaSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_media_command_stores_official_gallery_and_video(): void
    {
        $product = $this->createProduct([
            'name' => 'MikroTik GPeR',
            'slug' => 'mikrotik-gper',
            'sku' => 'GPeR',
        ]);

        Http::fake([
            'mikrotik.com/*' => Http::response($this->fakeProductPageHtml(), 200),
        ]);

        $this->artisan('mikrotik:sync-media')->assertExitCode(0);

        $product->refresh();

        $this->assertSame(
            'https://cdn.mikrotik.com/web-assets/rb_images/1791_lg.webp',
            $product->official_image_url
        );
        $this->assertSame([
            'https://cdn.mikrotik.com/web-assets/rb_images/1791_lg.webp',
            'https://cdn.mikrotik.com/web-assets/rb_images/1790_lg.webp',
            'https://cdn.mikrotik.com/web-assets/rb_images/1787_lg.webp',
        ], $product->official_gallery_images);
        $this->assertSame('https://www.youtube.com/watch?v=OPmM2i4sTu4', $product->official_video_url);
        $this->assertNotNull($product->official_media_synced_at);
    }

    public function test_sync_media_command_skips_products_without_official_pages(): void
    {
        $product = $this->createProduct([
            'name' => 'MikroTik Unknown',
            'slug' => 'mikrotik-unknown-model',
            'sku' => 'UNKNOWN',
        ]);

        Http::fake([
            'mikrotik.com/*' => Http::response('<html><head><title>Not Found</title></head><body></body></html>', 200),
        ]);

        $this->artisan('mikrotik:sync-media')->assertExitCode(0);

        $product->refresh();

        $this->assertNull($product->official_image_url);
        $this->assertNull($product->official_video_url);
        $this->assertNull($product->official_media_synced_at);
    }

    public function test_product_page_renders_official_gallery_and_video(): void
    {
        $product = $this->createProduct([
            'name' => 'MikroTik RB5009UG+S+IN',
            'slug' => 'mikrotik-rb5009ug-s-in',
            'sku' => 'RB5009UG+S+IN',
            'model_number' => 'RB5009UG+S+IN',
            'official_gallery_images' => [
                'https://cdn.mikrotik.com/web-assets/rb_images/2065_lg.webp',
                'https://cdn.mikrotik.com/web-assets/rb_images/2066_lg.webp',
            ],
            'official_video_url' => 'https://www.youtube.com/watch?v=OPmM2i4sTu4',
        ]);

        $response = $this->get('/product/'.$product->slug);

        $response->assertOk();
        $response->assertSee('https://cdn.mikrotik.com/web-assets/rb_images/2065_lg.webp', false);
        $response->assertSee('https://cdn.mikrotik.com/web-assets/rb_images/2066_lg.webp', false);
        $response->assertSee('https://www.youtube-nocookie.com/embed/OPmM2i4sTu4', false);
        $response->assertSee('Product video');
    }

    public function test_youtube_url_parsing_supports_common_formats(): void
    {
        $this->assertSame('OPmM2i4sTu4', ProductSeo::youtubeVideoId('https://www.youtube.com/watch?v=OPmM2i4sTu4'));
        $this->assertSame('OPmM2i4sTu4', ProductSeo::youtubeVideoId('https://youtu.be/OPmM2i4sTu4'));
        $this->assertSame('OPmM2i4sTu4', ProductSeo::youtubeVideoId('https://www.youtube.com/embed/OPmM2i4sTu4'));
        $this->assertSame('https://www.youtube-nocookie.com/embed/OPmM2i4sTu4', ProductSeo::youtubeEmbedUrl('https://www.youtube.com/watch?v=OPmM2i4sTu4'));
        $this->assertNull(ProductSeo::youtubeVideoId('https://example.com/not-a-video'));
        $this->assertNull(ProductSeo::youtubeVideoId(null));
    }

    private function fakeProductPageHtml(): string
    {
        return '<html><head>'
            .'<meta property="og:image" content="https://cdn.mikrotik.com/web-assets/rb_images/1791_tm.webp" />'
            .'</head><body>'
            .'<div wire:name="widgets.product-gallery">'
            .'<img src="https://cdn.mikrotik.com/web-assets/rb_images/1791_lg.webp" alt="GPeR" />'
            .'<img src="https://cdn.mikrotik.com/web-assets/rb_images/1791_xl.webp" alt="GPeR" />'
            .'<img src="https://cdn.mikrotik.com/web-assets/rb_images/1790_lg.webp" alt="GPeR" />'
            .'<img src="https://cdn.mikrotik.com/web-assets/rb_images/1787_lg.webp" alt="GPeR" />'
            .'</div>'
            .'<a href="https://www.youtube.com/watch?v=OPmM2i4sTu4" title="Watch Video"></a>'
            .'</body></html>';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes = []): Product
    {
        $category = Category::create([
            'name' => 'MikroTik Router Prices in Kenya',
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

        return Product::create(array_merge([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'MikroTik Router',
            'slug' => 'mikrotik-router',
            'description' => '<p>Reliable routing hardware for Kenyan networks.</p>',
            'price' => '5000.00',
            'stock' => 10,
            'sku' => 'RB-TEST',
            'status' => 'active',
        ], $attributes));
    }
}
