<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (! Schema::hasColumn('homepage_contents', 'contact_email')) {
                $table->string('contact_email', 190)->nullable()->after('contact_whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (Schema::hasColumn('homepage_contents', 'contact_email')) {
                $table->dropColumn('contact_email');
            }
        });
    }
};
