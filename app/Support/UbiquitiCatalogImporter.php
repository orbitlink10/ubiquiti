<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Str;

class UbiquitiCatalogImporter
{
    private const CATALOG_VENDOR_NAME = 'Ubiquiti UniFi Kenya Store';

    private const CATALOG_VENDOR_SLUG = 'ubiquiti-unifi-kenya-store';

    /**
     * @return array{created: int, updated: int}
     */
    public function importCategories(): array
    {
        $created = 0;
        $updated = 0;
        $bySlug = [];

        foreach (UbiquitiNetworkingCatalog::categories() as [$name, $slug, $parent, $seoTitle, $h1, $keyword, $meta]) {
            $parentId = null;

            if ($parent !== null && isset($bySlug[$parent])) {
                $parentId = $bySlug[$parent];
            }

            $payload = [
                'name' => $name,
                'slug' => $slug,
                'parent_id' => $parentId,
            ];

            foreach (['seo_title' => $seoTitle, 'h1' => $h1, 'focus_keyword' => $keyword, 'meta_description' => $meta] as $field => $value) {
                if ($value !== null && $value !== '') {
                    $payload[$field] = $value;
                }
            }

            $category = Category::where('slug', $slug)->first();

            if ($category) {
                $category->update($payload);
                $updated++;
            } else {
                $category = Category::create($payload);
                $created++;
            }

            $bySlug[$slug] = $category->id;
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * @return array{created: int, updated: int, skipped: int, report: array<int, array<string, mixed>>}
     */
    public function importProducts(): array
    {
        $vendor = $this->catalogVendor();
        $categories = Category::all()->keyBy('slug');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $report = [];

        foreach (UbiquitiNetworkingCatalog::products() as $data) {
            $slug = $data['slug'] ?? null;
            if ($slug === null || $slug === '') {
                $skipped++;
                $report[] = ['product' => $data['name'] ?? '(unknown)', 'status' => 'skipped', 'reason' => 'Missing slug'];
                continue;
            }

            $primary = $categories->get($data['category'] ?? '');
            if (! $primary) {
                $skipped++;
                $report[] = ['product' => $data['name'], 'status' => 'skipped', 'reason' => 'Primary category missing: '.($data['category'] ?? '')];
                continue;
            }

            $categoryIds = collect([$primary->id]);
            foreach ((array) ($data['categories'] ?? []) as $categorySlug) {
                if ($additional = $categories->get($categorySlug)) {
                    $categoryIds->push($additional->id);
                }
            }
            $categoryIds = $categoryIds->unique()->values()->all();

            $price = UbiquitiNetworkingCatalog::kenyanPrices()[$slug] ?? null;

            $attributes = $this->productAttributes($data, $primary, $price);

            $existing = Product::where('slug', $slug)->first();

            if (! $existing) {
                $existing = $this->findExistingBySkuOrModel($data);
            }

            if ($existing) {
                $existing->update($this->missingFields($existing, $attributes));
                $product = $existing;
                $updated++;
            } else {
                $attributes['vendor_id'] = $vendor->id;
                $attributes['category_id'] = $primary->id;
                $attributes['price'] = $price;
                $attributes['compare_at_price'] = null;
                $attributes['stock'] = 0;
                $attributes['status'] = 'active';
                $product = Product::create($attributes);
                $created++;
            }

            if (Product::categoryAssignmentsReady()) {
                $product->categories()->sync($categoryIds);
            }

            $report[] = [
                'product' => $data['name'],
                'slug' => $slug,
                'status' => $existing ? 'updated' : 'created',
                'category' => $primary->slug,
                'subcategories' => (array) ($data['categories'] ?? []),
                'manufacturer_url' => $data['manufacturer_url'] ?? null,
                'manufacturer_image_url' => $data['manufacturer_image_url'] ?? null,
            ];
        }

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped, 'report' => $report];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function productAttributes(array $data, Category $primary, ?float $price): array
    {
        $attributes = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'h1' => $data['h1'] ?? null,
            'focus_keyword' => ($data['focus_keyword'] ?? null) ?: trim($data['name']).' Kenya',
            'brand' => $data['brand'] ?? 'Ubiquiti',
            'model_number' => $data['model'] ?? null,
            'sku' => $data['sku'] ?? null,
            'description' => $data['description'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'key_use' => $data['key_use'] ?? null,
            'key_specifications' => $data['key_specifications'] ?? null,
            'technical_specifications' => $data['technical_specifications'] ?? null,
            'official_image_url' => $data['official_image_url'] ?? null,
            'official_gallery_images' => $data['official_gallery_images'] ?? null,
            'manufacturer_url' => $data['manufacturer_url'] ?? null,
            'manufacturer_image_url' => $data['manufacturer_image_url'] ?? null,
            'manufacturer_last_checked_at' => now(),
            'price' => $price,
            'mpn' => MerchantCatalog::cleanMpn($data['sku'] ?? $data['model'] ?? null),
            'google_product_category' => MerchantCatalog::googleCategories()[$data['category'] ?? ''] ?? null,
            'product_type' => MerchantCatalog::productTypes()[$data['category'] ?? ''] ?? null,
        ];

        return array_filter($attributes, fn ($value): bool => $value !== null);
    }

    /**
     * Match a pre-existing product by SKU or model number when the seed slug
     * does not match, so manually entered products do not collide with the
     * unique SKU constraint on re-seed.
     *
     * @param  array<string, mixed>  $data
     */
    private function findExistingBySkuOrModel(array $data): ?Product
    {
        foreach (['sku' => 'sku', 'model' => 'model_number'] as $dataKey => $column) {
            $value = trim((string) ($data[$dataKey] ?? ''));

            if ($value === '' || ! SeoMetadata::columnReady('products', $column)) {
                continue;
            }

            if ($product = Product::where($column, $value)->first()) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Only return fields the existing record does not already have, so admin
     * entered data (price, stock, SKU, description, images, etc.) is preserved.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function missingFields(Product $product, array $attributes): array
    {
        $updates = [];

        foreach ($attributes as $field => $value) {
            $current = $product->{$field} ?? null;

            if (is_array($value)) {
                if (empty($current)) {
                    $updates[$field] = $value;
                }
                continue;
            }

            $currentText = trim((string) $current);

            if ($currentText === '' || $currentText === '0') {
                $updates[$field] = $value;
            }
        }

        return $updates;
    }

    private function catalogVendor(): Vendor
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Marketplace Admin',
                'phone' => '+254700000001',
                'role' => 'admin',
                'status' => 'active',
                'password' => bcrypt('admin123'),
            ]
        );

        if (! $admin->isAdmin()) {
            $admin->update(['role' => 'admin']);
        }

        $vendor = Vendor::where('user_id', $admin->id)->first();

        if ($vendor) {
            $updates = ['is_approved' => true];

            if (Str::slug($vendor->shop_name) !== self::CATALOG_VENDOR_SLUG) {
                $updates['shop_name'] = self::CATALOG_VENDOR_NAME;
                $updates['slug'] = $this->uniqueVendorSlug();
            }

            $vendor->update($updates);

            return $vendor->refresh();
        }

        return Vendor::create([
            'user_id' => $admin->id,
            'shop_name' => self::CATALOG_VENDOR_NAME,
            'slug' => $this->uniqueVendorSlug(),
            'description' => 'Ubiquiti UniFi networking catalogue for Kenya.',
            'phone' => 'Kenya desk',
            'address' => 'Nairobi, Kenya',
            'is_approved' => true,
        ]);
    }

    private function uniqueVendorSlug(): string
    {
        $base = self::CATALOG_VENDOR_SLUG;
        $slug = $base;
        $counter = 1;

        while (Vendor::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
