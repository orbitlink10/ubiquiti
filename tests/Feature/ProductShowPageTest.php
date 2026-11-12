<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_show_page_uses_the_new_template_layout(): void
    {
        [$product, $customer] = $this->createApprovedProduct();

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'https://example.com/product-side.jpg',
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($customer)->get(route('product.show', $product));

        $response->assertOk();
        $response->assertSee('Product details');
        $response->assertSee('Additional information');
        $response->assertSee('Reviews (0)');
        $response->assertSee('Buy Now');
        $response->assertSee('Add to Cart');
        $response->assertSee('Compact backup power station with dependable output.');
        $response->assertSee('Availability:');
        $response->assertSee('AVAILABLE IN STORE');
        $response->assertSee('https://example.com/product-main.jpg', false);
        $response->assertSee('https://example.com/product-side.jpg', false);
    }

    public function test_buy_now_redirects_authenticated_user_to_checkout(): void
    {
        [$product, $customer] = $this->createApprovedProduct();

        $response = $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 1,
            'redirect' => 'checkout',
        ]);

        $response->assertRedirect('/checkout');
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_product_show_page_prompts_guest_to_log_in(): void
    {
        [$product] = $this->createApprovedProduct();

        $response = $this->get(route('product.show', $product));

        $response->assertOk();
        $response->assertSee('Add to Cart');
        $response->assertDontSee('name="redirect" value="checkout"', false);
    }

    public function test_product_show_page_uses_local_placeholder_when_product_has_no_images(): void
    {
        [$product] = $this->createApprovedProduct();
        $product->images()->delete();

        $response = $this->get(route('product.show', $product));

        $response->assertOk();
        $response->assertSee('/assets/product-placeholder.svg', false);
        $response->assertDontSee('via.placeholder.com', false);
    }

    public function test_product_show_page_uses_official_mikrotik_image_when_known_product_has_no_images(): void
    {
        [$product] = $this->createApprovedProduct();
        $product->update([
            'name' => 'Mikrotik GrooveA 52 ac',
            'slug' => 'mikrotik-groovea-52-ac',
        ]);
        $product->images()->delete();

        $response = $this->get(route('product.show', $product));

        $response->assertOk();
        $response->assertSee('src="https://cdn.mikrotik.com/web-assets/rb_images/1208_lg.webp"', false);
    }

    public function test_product_show_page_renders_legacy_public_product_image_paths(): void
    {
        [$product] = $this->createApprovedProduct();
        $product->images()->update(['image_url' => 'public/uploads/products/product-main.jpg']);

        $uploadedPath = public_path('uploads/products/product-main.jpg');
        File::ensureDirectoryExists(dirname($uploadedPath));
        File::put($uploadedPath, 'test image');

        try {
            $response = $this->get(route('product.show', $product));

            $response->assertOk();
            $response->assertSee('src="/uploads/products/product-main.jpg"', false);
            $response->assertDontSee('src="/assets/product-placeholder.svg"', false);
        } finally {
            File::delete($uploadedPath);
        }
    }

    public function test_product_show_page_renders_absolute_public_product_image_paths(): void
    {
        [$product] = $this->createApprovedProduct();
        $uploadedPath = public_path('uploads/products/product-main.jpg');
        $product->images()->update(['image_url' => $uploadedPath]);

        File::ensureDirectoryExists(dirname($uploadedPath));
        File::put($uploadedPath, 'test image');

        try {
            $response = $this->get(route('product.show', $product));

            $response->assertOk();
            $response->assertSee('src="/uploads/products/product-main.jpg"', false);
            $response->assertDontSee('src="/assets/product-placeholder.svg"', false);
        } finally {
            File::delete($uploadedPath);
        }
    }

    public function test_product_show_page_ignores_missing_local_product_image_and_uses_official_fallback(): void
    {
        [$product] = $this->createApprovedProduct();
        $product->update([
            'name' => 'MikroTik RB4011iGS+RM',
            'slug' => 'mikrotik-rb4011igsrm',
        ]);
        $product->images()->update(['image_url' => '/uploads/products/missing-rb4011.webp']);

        $response = $this->get(route('product.show', $product));

        $response->assertOk();
        $response->assertSee('src="https://cdn.mikrotik.com/web-assets/rb_images/1633_lg.webp"', false);
        $response->assertDontSee('src="/uploads/products/missing-rb4011.webp"', false);
    }

    /**
     * @return array{0: \App\Models\Product, 1: \App\Models\User}
     */
    private function createApprovedProduct(): array
    {
        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'status' => 'active',
            'phone' => '0712345678',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Power Hub Kenya',
            'slug' => 'power-hub-kenya',
            'description' => 'Reliable power and network gear.',
            'phone' => '0712345678',
            'address' => 'Westlands, Nairobi',
            'is_approved' => true,
        ]);

        $category = Category::create([
            'name' => 'Portable Power',
            'slug' => 'portable-power',
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Bluetti AC2P Portable Power Station',
            'slug' => 'bluetti-ac2p',
            'description' => '<p>Compact backup power for home, travel, and field work.</p><ul><li>Lightweight body</li><li>Reliable battery output</li></ul>',
            'meta_description' => 'Compact backup power station with dependable output.',
            'price' => '20000.00',
            'compare_at_price' => '25000.00',
            'stock' => 6,
            'sku' => 'SKU-AC2P',
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'https://example.com/product-main.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        return [$product, $customer];
    }
}
