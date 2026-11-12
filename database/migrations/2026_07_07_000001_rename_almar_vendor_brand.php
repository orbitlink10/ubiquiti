<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendors')) {
            return;
        }

        $legacyOfficial = 'Official';

        DB::table('vendors')
            ->where('shop_name', 'Almar Market '.$legacyOfficial.' Store')
            ->update([
                'shop_name' => 'Mikrotik Kenya Store',
                'slug' => 'mikrotik-kenya-store',
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('vendors')) {
            return;
        }

        $legacyOfficial = 'Official';

        DB::table('vendors')
            ->where('shop_name', 'Mikrotik Kenya '.$legacyOfficial.' Store')
            ->where('slug', 'mikrotik-kenya-official-store')
            ->update([
                'shop_name' => 'Almar Market Store',
                'slug' => 'almar-market-store',
            ]);

        DB::table('vendors')
            ->where('shop_name', 'Mikrotik Kenya Store')
            ->where('slug', 'mikrotik-kenya-store')
            ->update([
                'shop_name' => 'Almar Market Store',
                'slug' => 'almar-market-store',
            ]);
    }
};
