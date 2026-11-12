<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (! Schema::hasColumn('homepage_contents', 'contact_phone')) {
                $table->string('contact_phone', 40)->nullable()->after('site_logo_path');
            }

            if (! Schema::hasColumn('homepage_contents', 'contact_whatsapp')) {
                $table->string('contact_whatsapp', 40)->nullable()->after('contact_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['contact_phone', 'contact_whatsapp'],
                fn (string $column): bool => Schema::hasColumn('homepage_contents', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
