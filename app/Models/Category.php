<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'h1',
        'focus_keyword',
        'meta_description',
        'slug',
        'parent_id',
        'image_url',
        'description',
        'seo_title',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'intro',
        'seo_content',
        'faq_items',
    ];

    protected $casts = [
        'faq_items' => 'array',
    ];

    public static function contentFieldsReady(): bool
    {
        $table = (new static)->getTable();

        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'meta_description')
            && Schema::hasColumn($table, 'description');
    }

    public static function seoFieldsReady(): bool
    {
        $table = (new static)->getTable();

        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'seo_title')
            && Schema::hasColumn($table, 'h1')
            && Schema::hasColumn($table, 'focus_keyword')
            && Schema::hasColumn($table, 'canonical_url')
            && Schema::hasColumn($table, 'intro')
            && Schema::hasColumn($table, 'seo_content')
            && Schema::hasColumn($table, 'faq_items');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function assignedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }
}
