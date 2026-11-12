<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (!Schema::hasColumn('products', 'official_image_url')) {
                $table->string('official_image_url', 500)->nullable();
            }

            if (!Schema::hasColumn('products', 'official_gallery_images')) {
                $table->json('official_gallery_images')->nullable();
            }

            if (!Schema::hasColumn('products', 'official_video_url')) {
                $table->string('official_video_url', 500)->nullable();
            }

            if (!Schema::hasColumn('products', 'official_media_synced_at')) {
                $table->timestamp('official_media_synced_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $columns = [
                'official_image_url',
                'official_gallery_images',
                'official_video_url',
                'official_media_synced_at',
            ];

            $existing = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('products', $column)));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
