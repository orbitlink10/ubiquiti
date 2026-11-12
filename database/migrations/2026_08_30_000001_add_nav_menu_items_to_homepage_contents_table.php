<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (! Schema::hasColumn('homepage_contents', 'nav_menu_items')) {
                $table->json('nav_menu_items')->nullable()->after('featured_product_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (Schema::hasColumn('homepage_contents', 'nav_menu_items')) {
                $table->dropColumn('nav_menu_items');
            }
        });
    }
};
