<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketplaceDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Marketplace Admin',
                'phone' => '+254700000001',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('admin123'),
            ]
        );

        $vendorUser = User::updateOrCreate(
            ['email' => 'vendor@almar.test'],
            [
                'name' => 'Vendor Owner',
                'phone' => '+254700000002',
                'role' => 'vendor',
                'status' => 'active',
                'password' => Hash::make('vendor123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@almar.test'],
            [
                'name' => 'Sample Customer',
                'phone' => '+254700000003',
                'role' => 'customer',
                'status' => 'active',
                'password' => Hash::make('customer123'),
            ]
        );

        $vendor = Vendor::updateOrCreate(
            ['user_id' => $vendorUser->id],
            [
                'shop_name' => 'Smart Deals Hub',
                'slug' => 'smart-deals-hub',
                'description' => 'Electronics and lifestyle products',
                'phone' => '+254700111222',
                'address' => 'Nairobi CBD',
                'is_approved' => true,
            ]
        );

        $samples = [
            [
                'category' => 'Phones & Tablets',
                'name' => 'Infinix Hot 40 Pro 256GB',
                'sku' => 'SKU-PHN-1001',
                'price' => 31999,
                'compare_at_price' => 35999,
                'stock' => 30,
                'image_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'TVs & Audio',
                'name' => 'Hisense 43 Inch Smart TV',
                'sku' => 'SKU-TV-1002',
                'price' => 38999,
                'compare_at_price' => 42999,
                'stock' => 18,
                'image_url' => 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Computing',
                'name' => 'HP EliteBook 840 G6',
                'sku' => 'SKU-CMP-1003',
                'price' => 55999,
                'compare_at_price' => 61999,
                'stock' => 12,
                'image_url' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Home & Office',
                'name' => 'Office Desk and Chair Combo',
                'sku' => 'SKU-HOF-1004',
                'price' => 14999,
                'compare_at_price' => 17999,
                'stock' => 20,
                'image_url' => 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Beauty',
                'name' => 'L Oreal Skincare Serum',
                'sku' => 'SKU-BEA-1005',
                'price' => 2499,
                'compare_at_price' => 2999,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Fashion',
                'name' => 'Men Casual Sneakers',
                'sku' => 'SKU-FSN-1006',
                'price' => 3299,
                'compare_at_price' => 3999,
                'stock' => 45,
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Appliances',
                'name' => 'Ramtons 20L Microwave',
                'sku' => 'SKU-APP-1007',
                'price' => 11999,
                'compare_at_price' => 13999,
                'stock' => 16,
                'image_url' => 'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category' => 'Gaming',
                'name' => 'PS5 DualSense Controller',
                'sku' => 'SKU-GAM-1008',
                'price' => 9499,
                'compare_at_price' => 10499,
                'stock' => 22,
                'image_url' => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?auto=format&fit=crop&w=900&q=80',
            ],
        ];

        foreach ($samples as $sample) {
            $category = Category::where('name', $sample['category'])->first();
            if (!$category) {
                continue;
            }

            $slugBase = Str::slug($sample['name']);
            $slug = $slugBase;
            $counter = 1;
            while (Product::where('slug', $slug)->where('vendor_id', '!=', $vendor->id)->exists()) {
                $slug = $slugBase . '-' . $counter;
                $counter++;
            }

            $product = Product::updateOrCreate(
                ['vendor_id' => $vendor->id, 'name' => $sample['name']],
                [
                    'category_id' => $category->id,
                    'slug' => $slug,
                    'description' => $sample['name'] . ' - high quality item for your marketplace.',
                    'price' => $sample['price'],
                    'compare_at_price' => $sample['compare_at_price'],
                    'stock' => $sample['stock'],
                    'sku' => $sample['sku'],
                    'status' => 'active',
                ]
            );

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'is_primary' => true],
                [
                    'image_url' => $sample['image_url'],
                    'sort_order' => 0,
                ]
            );
        }

        if (!$admin->isAdmin()) {
            $admin->update(['role' => 'admin']);
        }
    }
}
