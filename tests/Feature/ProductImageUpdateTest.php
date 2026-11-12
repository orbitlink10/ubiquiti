<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductImageUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_upload_preserves_an_existing_product_url(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $category = Category::create(['name' => 'airMAX', 'slug' => 'airmax']);
        $vendor = Vendor::create([
            'user_id' => $admin->id,
            'shop_name' => 'Admin Store',
            'slug' => 'admin-store',
            'phone' => '0700000000',
            'address' => 'Nairobi',
            'is_approved' => true,
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Ubiquiti LiteAP 120',
            'slug' => 'ubiquiti-airmax-liteap-120',
            'description' => '<p>Outdoor access point.</p>',
            'price' => '11500.00',
            'stock' => 0,
            'sku' => 'LAP-120',
            'status' => 'active',
        ]);
        $originalUrl = route('product.show', $product);

        try {
            $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'category_id' => $category->id,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'image' => UploadedFile::fake()->create('liteap.png', 64, 'image/png'),
            ]);

            $response->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));
            $product->refresh();
            $this->assertSame('ubiquiti-airmax-liteap-120', $product->slug);
            $this->assertSame($originalUrl, route('product.show', $product));
            $image = $product->images()->where('is_primary', true)->sole();
            $this->assertFileExists(public_path(ltrim($image->image_url, '/')));
            $this->get($originalUrl)->assertOk()->assertSee($image->image_url, false);
        } finally {
            foreach ($product->images()->get() as $image) {
                File::delete(public_path(ltrim($image->image_url, '/')));
            }
        }
    }
}
