<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Support\SeoMetadata;
use App\Support\UbiquitiSeoCatalog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('seo:consolidate-ubiquiti-categories {--apply : Persist category consolidation changes}', function (): int {
    $apply = (bool) $this->option('apply');
    $this->info($apply ? 'Applying Ubiquiti category consolidation.' : 'Dry run only. Re-run with --apply to persist changes.');

    $work = function () use ($apply): void {
        foreach (UbiquitiSeoCatalog::primaryCategories() as $slug => $categoryData) {
            $category = Category::query()->where('slug', $slug)->first();

            if (! $category && $apply) {
                $category = Category::create([
                    'name' => $categoryData['name'],
                    'slug' => $slug,
                    'meta_description' => $categoryData['meta_description'],
                    'description' => $categoryData['description'],
                ]);
            }

            $this->line(($category ? 'Found' : 'Would create').' primary category: '.$slug);
        }

        foreach (UbiquitiSeoCatalog::legacyCategoryRedirects() as $legacySlug => $targetSlug) {
            $source = Category::query()->where('slug', $legacySlug)->first();
            $target = Category::query()->where('slug', $targetSlug)->first();

            if (! $source) {
                continue;
            }

            if (! $target) {
                $this->warn('Skipping '.$legacySlug.' because target '.$targetSlug.' does not exist.');

                continue;
            }

            if ($source->is($target)) {
                continue;
            }

            $productCount = Product::query()->where('category_id', $source->id)->count();
            $childCount = Category::query()->where('parent_id', $source->id)->count();

            $this->line(($apply ? 'Merging' : 'Would merge').' '.$legacySlug.' -> '.$targetSlug.' (products: '.$productCount.', children: '.$childCount.')');

            if ($apply) {
                Product::query()->where('category_id', $source->id)->update(['category_id' => $target->id]);
                Category::query()->where('parent_id', $source->id)->update(['parent_id' => $target->id]);
                $source->delete();
            }
        }

        Product::query()
            ->with('category')
            ->whereHas('category', fn ($query) => $query->whereIn('slug', ['ubiquiti', 'ubiquiti-products', 'ubiquiti-products-in-kenya', 'ubiquity', 'ubiquity-products']))
            ->chunkById(100, function ($products) use ($apply): void {
                foreach ($products as $product) {
                    $targetSlug = UbiquitiSeoCatalog::productIntentSlug($product);
                    if (! $targetSlug) {
                        $this->warn('Needs manual category review: '.$product->name);

                        continue;
                    }

                    $target = Category::query()->where('slug', $targetSlug)->first();
                    if (! $target) {
                        continue;
                    }

                    $this->line(($apply ? 'Reassigning' : 'Would reassign').' '.$product->name.' -> '.$target->name);

                    if ($apply) {
                        $product->update(['category_id' => $target->id]);
                    }
                }
            });
    };

    $apply ? DB::transaction($work) : $work();

    $this->info('Done.');

    return 0;
})->purpose('Consolidate overlapping Ubiquiti category slugs into the primary SEO taxonomy');

Artisan::command('seo:audit', function (): int {
    $issues = [];

    Product::query()->with('category')->active()->chunkById(100, function ($products) use (&$issues): void {
        foreach ($products as $product) {
            if (! $product->category) {
                $issues[] = ['product', $product->slug, 'Missing category'];
            }

            if (trim(SeoMetadata::productDescription($product)) === '') {
                $issues[] = ['product', $product->slug, 'Missing meta description fallback'];
            }
        }
    });

    Category::query()->withCount('products')->chunkById(100, function ($categories) use (&$issues): void {
        foreach ($categories as $category) {
            if ($category->products_count === 0 && ! UbiquitiSeoCatalog::isBroadUbiquitiCategory($category)) {
                $issues[] = ['category', $category->slug, 'No directly assigned products'];
            }

            if (trim(SeoMetadata::categoryDescription($category)) === '') {
                $issues[] = ['category', $category->slug, 'Missing meta description fallback'];
            }
        }
    });

    Page::query()->chunkById(100, function ($pages) use (&$issues): void {
        foreach ($pages as $page) {
            if (trim((string) $page->meta_title) === '') {
                $issues[] = ['page', $page->slug, 'Missing meta title'];
            }

            if (trim((string) $page->meta_description) === '') {
                $issues[] = ['page', $page->slug, 'Missing meta description'];
            }
        }
    });

    if ($issues === []) {
        $this->info('No obvious SEO issues found in products, categories or pages.');

        return 0;
    }

    $this->table(['Type', 'URL key', 'Issue'], $issues);

    return 0;
})->purpose('Report missing SEO metadata, orphaned products and empty categories without changing data');
