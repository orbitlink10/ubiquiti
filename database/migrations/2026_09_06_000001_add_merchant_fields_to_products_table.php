<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'gtin')) {
                $table->string('gtin', 64)->nullable()->after('sku');
            }

            if (! Schema::hasColumn('products', 'mpn')) {
                $table->string('mpn', 128)->nullable()->after('gtin');
            }

            if (! Schema::hasColumn('products', 'google_product_category')) {
                $table->string('google_product_category', 255)->nullable()->after('mpn');
            }

            if (! Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type', 255)->nullable()->after('google_product_category');
            }

            if (! Schema::hasColumn('products', 'condition')) {
                $table->string('condition', 40)->default('new')->after('product_type');
            }

            if (! Schema::hasColumn('products', 'identifier_exists')) {
                $table->boolean('identifier_exists')->default(true)->after('condition');
            }

            if (! Schema::hasColumn('products', 'include_in_merchant_feed')) {
                $table->boolean('include_in_merchant_feed')->default(true)->after('identifier_exists');
            }

            if (! Schema::hasColumn('products', 'merchant_title')) {
                $table->string('merchant_title', 180)->nullable()->after('include_in_merchant_feed');
            }

            if (! Schema::hasColumn('products', 'merchant_description')) {
                $table->text('merchant_description')->nullable()->after('merchant_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                [
                    'gtin',
                    'mpn',
                    'google_product_category',
                    'product_type',
                    'condition',
                    'identifier_exists',
                    'include_in_merchant_feed',
                    'merchant_title',
                    'merchant_description',
                ],
                fn (string $column): bool => Schema::hasColumn('products', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
