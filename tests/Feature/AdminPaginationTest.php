<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_navigation_reaches_remaining_products_and_preserves_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $category = Category::create(['name' => 'Networking', 'slug' => 'networking']);
        $vendor = Vendor::create([
            'user_id' => $admin->id,
            'shop_name' => 'Test Shop', 'slug' => 'test-shop',
            'phone' => '0700000000', 'address' => 'Nairobi',
        ]);

        for ($i = 1; $i <= 21; $i++) {
            Product::create([
                'vendor_id' => $vendor->id, 'category_id' => $category->id,
                'name' => "Router item $i", 'slug' => "router-$i",
                'sku' => "ROUTER-$i", 'price' => 1000,
            ]);
        }

        $first = $this->actingAs($admin)->get('/admin/products?search=Router');
        $first->assertOk()->assertSee('Page 1 of 2 (21 total)')
            ->assertSee('aria-disabled="true">Previous', false)
            ->assertSee('rel="next">Next', false)
            ->assertSee('search=Router&amp;page=2', false);
        $first->assertViewHas('products', fn ($products) => $products->count() === 20);

        $last = $this->get('/admin/products?search=Router&page=2');
        $last->assertOk()->assertSee('Router item 1')->assertDontSee('Router item 21')
            ->assertSee('Page 2 of 2 (21 total)')
            ->assertSee('search=Router&amp;page=1', false)
            ->assertSee('aria-disabled="true">Next', false);
        $last->assertViewHas('products', fn ($products) => $products->count() === 1);

        $this->get('/admin/products?search=Missing')->assertOk()
            ->assertSee('No products found.')->assertDontSee('aria-label="Products pagination"', false);
    }

    public function test_pages_and_posts_navigation_reaches_remaining_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        for ($i = 1; $i <= 22; $i++) {
            Page::create([
                'title' => "Content item $i", 'slug' => "content-$i",
                'type' => $i % 2 ? 'page' : 'post',
            ]);
        }

        $first = $this->actingAs($admin)->get('/admin/pages');
        $first->assertOk()->assertSee('Page 1 of 2 (22 total)')
            ->assertSee('href="'.url('/admin/pages?page=2').'"', false);
        $first->assertViewHas('pages', fn ($pages) => $pages->count() === 20);

        $last = $this->get('/admin/pages?page=2');
        $last->assertOk()->assertSee('Content item 1')->assertSee('Content item 2')
            ->assertDontSee('Content item 22')->assertSee('Page 2 of 2 (22 total)')
            ->assertSee('rel="prev">Previous', false)
            ->assertSee('aria-disabled="true">Next', false);
        $last->assertViewHas('pages', fn ($pages) => $pages->pluck('type')->sort()->values()->all() === ['page', 'post']);
    }
}
