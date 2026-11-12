<?php

namespace App\Models;

use App\Support\ProductImageCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_url',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function publicUrl(): ?string
    {
        $imageUrl = trim((string) $this->image_url);
        if ($imageUrl === '') {
            return null;
        }

        if (preg_match('#^(?:https?:)?//#i', $imageUrl) === 1 || Str::startsWith($imageUrl, 'data:image/')) {
            return $imageUrl;
        }

        $imageUrl = str_replace('\\', '/', $imageUrl);
        $imageUrl = preg_replace('#/+#', '/', $imageUrl) ?? $imageUrl;
        $imageUrl = preg_replace('#^\./#', '', $imageUrl) ?? $imageUrl;

        $publicPath = $this->pathRelativeToRoot($imageUrl, public_path());
        if ($publicPath !== null) {
            return $this->publicFileUrlIfExists($publicPath);
        }

        $storagePath = $this->pathRelativeToRoot($imageUrl, storage_path('app/public'));
        if ($storagePath !== null) {
            return $this->storageFileUrlIfExists($storagePath, 'app/public');
        }

        if (preg_match('#^/?(?:storage/app/public|app/public)/(.*)$#i', $imageUrl, $matches) === 1) {
            return $this->storageFileUrlIfExists($matches[1], 'app/public');
        }

        $imageUrl = preg_replace('#^/?public/#', '', $imageUrl) ?? $imageUrl;

        foreach (['/public_html/', '/public/'] as $marker) {
            $position = stripos($imageUrl, $marker);
            if ($position !== false) {
                $imageUrl = substr($imageUrl, $position + strlen($marker));
                break;
            }
        }

        if (Str::startsWith($imageUrl, '/')) {
            return $this->publicFileUrlIfExists($imageUrl);
        }

        return $this->publicFileUrlIfExists($imageUrl);
    }

    private function pathRelativeToRoot(string $path, string $root): ?string
    {
        $normalizedRoot = str_replace('\\', '/', $root);
        $normalizedRoot = rtrim(preg_replace('#/+#', '/', $normalizedRoot) ?? $normalizedRoot, '/');
        $normalizedPath = $path;

        if (!Str::startsWith(Str::lower($normalizedPath), Str::lower($normalizedRoot . '/'))) {
            return null;
        }

        return ltrim(substr($normalizedPath, strlen($normalizedRoot)), '/');
    }

    private function publicFileUrlIfExists(string $path): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', $path), '/');

        if ($relativePath === '' || Str::contains($relativePath, '..')) {
            return null;
        }

        if (!is_file(public_path($relativePath))) {
            if (!$this->storageUploadFileExists($relativePath)) {
                return null;
            }
        }

        return ProductImageCatalog::publicPathUrl($relativePath);
    }

    private function storageFileUrlIfExists(string $path, string $storageRoot): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', $path), '/');

        if ($relativePath === '' || Str::contains($relativePath, '..')) {
            return null;
        }

        if (!is_file(storage_path(trim($storageRoot, '/') . '/' . $relativePath))) {
            return null;
        }

        if (Str::startsWith($relativePath, 'uploads/products/')) {
            return ProductImageCatalog::publicPathUrl($relativePath);
        }

        return ProductImageCatalog::publicPathUrl('storage/' . $relativePath);
    }

    private function storageUploadFileExists(string $relativePath): bool
    {
        if (!Str::startsWith($relativePath, 'uploads/products/')) {
            return false;
        }

        return is_file(storage_path('app/public/' . $relativePath))
            || is_file(storage_path('app/' . $relativePath));
    }
}
