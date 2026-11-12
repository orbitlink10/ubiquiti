<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (!Schema::hasColumn('homepage_contents', 'site_logo_path')) {
                $table->string('site_logo_path')->nullable()->after('site_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (Schema::hasColumn('homepage_contents', 'site_logo_path')) {
                $table->dropColumn('site_logo_path');
            }
        });
    }
};
