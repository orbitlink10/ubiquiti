<?php

namespace Database\Seeders;

use App\Support\UbiquitiCatalogImporter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UbiquitiNetworkingSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $importer = new UbiquitiCatalogImporter;

        $importer->importCategories();
        $importer->importProducts();
    }
}
