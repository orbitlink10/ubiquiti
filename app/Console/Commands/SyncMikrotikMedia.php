<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\ProductImageCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncMikrotikMedia extends Command
{
    protected $signature = 'mikrotik:sync-media
        {--limit=100 : Maximum number of products to sync}
        {--only-slug= : Sync a single product by slug}
        {--force : Re-sync products that already have official media}';

    protected $description = 'Sync official images, galleries and videos from mikrotik.com into product records';

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

    private const PRODUCT_PAGE_URL = 'https://mikrotik.com/product/';

    private const GALLERY_LIMIT = 6;

    public function handle(): int
    {
        $query = Product::query()->active();

        if ($onlySlug = $this->option('only-slug')) {
            $query->where('slug', $onlySlug);
        }

        if (! $this->option('force')) {
            $query->whereNull('official_media_synced_at');
        }

        $products = $query->limit((int) $this->option('limit'))->get();

        if ($products->isEmpty()) {
            $this->info('No products to sync.');

            return self::SUCCESS;
        }

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($products as $product) {
            try {
                $media = $this->fetchOfficialMedia($product);

                if ($media === null) {
                    $skipped++;
                    $this->line("<comment>skip</comment> {$product->slug} (no official page found)");

                    continue;
                }

                $product->update($media + ['official_media_synced_at' => now()]);
                $synced++;
                $this->line("<info>sync</info> {$product->slug} images=".count($media['official_gallery_images']).' video='.($media['official_video_url'] ? 'yes' : 'no'));
            } catch (\Throwable $throwable) {
                $failed++;
                $this->line("<error>fail</error> {$product->slug} {$throwable->getMessage()}");
            }

            usleep(250000);
        }

        $this->info("Done. Synced: {$synced}, skipped: {$skipped}, failed: {$failed}.");

        return self::SUCCESS;
    }

    /**
     * @return array{official_image_url: ?string, official_gallery_images: array<int, string>, official_video_url: ?string}|null
     */
    private function fetchOfficialMedia(Product $product): ?array
    {
        foreach ($this->candidateOfficialSlugs($product) as $candidateSlug) {
            $html = $this->fetchProductPage($candidateSlug);

            if ($html === null) {
                continue;
            }

            $gallery = $this->extractGallery($html);
            $videoUrl = $this->extractVideoUrl($html);

            if ($gallery === []) {
                continue;
            }

            return [
                'official_image_url' => $gallery[0],
                'official_gallery_images' => $gallery,
                'official_video_url' => $videoUrl,
            ];
        }

        $staticImage = ProductImageCatalog::officialUrlFor($product->name);
        if ($staticImage) {
            return [
                'official_image_url' => $staticImage,
                'official_gallery_images' => [$staticImage],
                'official_video_url' => null,
            ];
        }

        return null;
    }

    private function fetchProductPage(string $slug): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml',
            ])->timeout(20)->retry(2, 500, throw: false)->get(self::PRODUCT_PAGE_URL.$slug);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $html = $response->body();

        if (str_contains($html, '<title>Not Found</title>')) {
            return null;
        }

        return $html;
    }

    /**
     * @return array<int, string>
     */
    private function candidateOfficialSlugs(Product $product): array
    {
        $candidates = [];
        $slug = Str::slug((string) $product->slug);

        if ($slug !== '') {
            $candidates[] = $slug;
        }

        if (Str::startsWith($slug, 'mikrotik-')) {
            $candidates[] = Str::after($slug, 'mikrotik-');
        }

        $name = trim(preg_replace('/\s+/u', ' ', (string) $product->name) ?? '');
        $name = preg_replace('/^mikrotik\s+/i', '', $name) ?? $name;
        $nameSlug = Str::slug($name);

        if ($nameSlug !== '' && ! in_array($nameSlug, $candidates, true)) {
            $candidates[] = $nameSlug;
        }

        $model = trim((string) $product->model_number);
        if ($model !== '') {
            $modelSlug = Str::slug($model);
            if ($modelSlug !== '' && ! in_array($modelSlug, $candidates, true)) {
                $candidates[] = $modelSlug;
            }
        }

        $candidates = array_values(array_unique(array_filter($candidates)));

        $variants = [];
        foreach ($candidates as $candidate) {
            $variants[] = str_replace('-', '_', $candidate);
            $variants[] = strtoupper($candidate);
        }

        return array_values(array_unique(array_merge($variants, $candidates)));
    }

    /**
     * @return array<int, string>
     */
    private function extractGallery(string $html): array
    {
        $galleryMarker = 'widgets.product-gallery';
        $position = strpos($html, $galleryMarker);

        if ($position === false) {
            return [];
        }

        $gallerySection = substr($html, $position, 200000);

        preg_match_all('/rb_images\/(\d+)_(?:lg|xl)\.webp/', $gallerySection, $matches);

        $ids = [];
        foreach ($matches[1] as $id) {
            if (! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return array_slice(array_map(
            fn (string $id): string => 'https://cdn.mikrotik.com/web-assets/rb_images/'.$id.'_lg.webp',
            $ids
        ), 0, self::GALLERY_LIMIT);
    }

    private function extractVideoUrl(string $html): ?string
    {
        if (preg_match('/https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([A-Za-z0-9_-]{6,})/', $html, $matches)) {
            return 'https://www.youtube.com/watch?v='.$matches[1];
        }

        if (preg_match('/https?:\/\/(?:www\.)?youtube-nocookie\.com\/embed\/([A-Za-z0-9_-]{6,})/', $html, $matches)) {
            return 'https://www.youtube.com/watch?v='.$matches[1];
        }

        return null;
    }
}
