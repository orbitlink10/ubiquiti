<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('meta_title', 180)->nullable()->after('id');
            $table->string('meta_description', 255)->nullable()->after('meta_title');
            $table->string('heading_two', 180)->nullable()->after('alt_text');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn(['meta_title', 'meta_description', 'heading_two']);
        });
    }
};
