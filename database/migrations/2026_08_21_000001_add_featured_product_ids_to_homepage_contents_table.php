<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (!Schema::hasColumn('homepage_contents', 'featured_product_ids')) {
                $table->longText('featured_product_ids')->nullable()->after('content_body');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (Schema::hasColumn('homepage_contents', 'featured_product_ids')) {
                $table->dropColumn('featured_product_ids');
            }
        });
    }
};
