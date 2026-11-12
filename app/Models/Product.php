<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'h1',
        'focus_keyword',
        'slug',
        'description',
        'meta_description',
        'price',
        'compare_at_price',
        'stock',
        'sku',
        'gtin',
        'mpn',
        'google_product_category',
        'product_type',
        'condition',
        'identifier_exists',
        'include_in_merchant_feed',
        'merchant_title',
        'merchant_description',
        'status',
        'seo_title',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'model_number',
        'brand',
        'key_use',
        'key_specifications',
        'use_cases',
        'technical_specifications',
        'whats_in_box',
        'recommended_applications',
        'choose_another_model',
        'compatibility',
        'power_requirements',
        'warranty_info',
        'delivery_info',
        'payment_info',
        'faq_items',
        'official_image_url',
        'official_gallery_images',
        'official_video_url',
        'official_media_synced_at',
        'manufacturer_url',
        'manufacturer_image_url',
        'manufacturer_last_checked_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'faq_items' => 'array',
        'official_gallery_images' => 'array',
        'official_media_synced_at' => 'datetime',
        'manufacturer_last_checked_at' => 'datetime',
        'identifier_exists' => 'boolean',
        'include_in_merchant_feed' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function seoFieldsReady(): bool
    {
        $table = (new static)->getTable();

        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'seo_title')
            && Schema::hasColumn($table, 'h1')
            && Schema::hasColumn($table, 'focus_keyword')
            && Schema::hasColumn($table, 'model_number')
            && Schema::hasColumn($table, 'faq_items');
    }

    public static function categoryAssignmentsReady(): bool
    {
        return Schema::hasTable('category_product');
    }

    public static function officialMediaFieldsReady(): bool
    {
        $table = (new static)->getTable();

        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'official_image_url')
            && Schema::hasColumn($table, 'official_gallery_images')
            && Schema::hasColumn($table, 'official_video_url');
    }

    public static function manufacturerSourceFieldsReady(): bool
    {
        $table = (new static)->getTable();

        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'manufacturer_url')
            && Schema::hasColumn($table, 'manufacturer_image_url')
            && Schema::hasColumn($table, 'manufacturer_last_checked_at');
    }

    public static function merchantFieldsReady(): bool
    {
        $table = (new static)->getTable();

        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'gtin')
            && Schema::hasColumn($table, 'mpn')
            && Schema::hasColumn($table, 'google_product_category')
            && Schema::hasColumn($table, 'product_type')
            && Schema::hasColumn($table, 'condition')
            && Schema::hasColumn($table, 'identifier_exists')
            && Schema::hasColumn($table, 'include_in_merchant_feed');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_primary', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereHas('vendor', fn (Builder $builder) => $builder->where('is_approved', true));
    }
}
