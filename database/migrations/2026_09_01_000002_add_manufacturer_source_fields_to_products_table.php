<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'manufacturer_url')) {
                $table->string('manufacturer_url', 500)->nullable()->after('official_media_synced_at');
            }

            if (! Schema::hasColumn('products', 'manufacturer_image_url')) {
                $table->string('manufacturer_image_url', 500)->nullable()->after('manufacturer_url');
            }

            if (! Schema::hasColumn('products', 'manufacturer_last_checked_at')) {
                $table->timestamp('manufacturer_last_checked_at')->nullable()->after('manufacturer_image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['manufacturer_url', 'manufacturer_image_url', 'manufacturer_last_checked_at'],
                fn (string $column): bool => Schema::hasColumn('products', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
