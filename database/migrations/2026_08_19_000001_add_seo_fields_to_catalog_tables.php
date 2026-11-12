<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $this->addSeoColumns($table, 'categories');

            if (! Schema::hasColumn('categories', 'intro')) {
                $table->text('intro')->nullable()->after('description');
            }

            if (! Schema::hasColumn('categories', 'seo_content')) {
                $table->longText('seo_content')->nullable()->after('intro');
            }

            if (! Schema::hasColumn('categories', 'faq_items')) {
                $table->json('faq_items')->nullable()->after('seo_content');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            $this->addSeoColumns($table, 'products');

            foreach ([
                'model_number',
                'brand',
                'key_use',
                'key_specifications',
                'use_cases',
                'technical_specifications',
                'whats_in_box',
                'recommended_applications',
                'choose_another_model',
                'compatibility',
                'power_requirements',
                'warranty_info',
                'delivery_info',
                'payment_info',
            ] as $column) {
                if (! Schema::hasColumn('products', $column)) {
                    $table->text($column)->nullable();
                }
            }

            if (! Schema::hasColumn('products', 'faq_items')) {
                $table->json('faq_items')->nullable();
            }
        });

        Schema::table('pages', function (Blueprint $table): void {
            $this->addSeoColumns($table, 'pages');

            if (! Schema::hasColumn('pages', 'faq_items')) {
                $table->json('faq_items')->nullable();
            }
        });
    }

    public function down(): void
    {
        foreach ([
            'categories' => ['seo_title', 'canonical_url', 'robots', 'og_title', 'og_description', 'og_image', 'intro', 'seo_content', 'faq_items'],
            'products' => [
                'seo_title',
                'canonical_url',
                'robots',
                'og_title',
                'og_description',
                'og_image',
                'model_number',
                'brand',
                'key_use',
                'key_specifications',
                'use_cases',
                'technical_specifications',
                'whats_in_box',
                'recommended_applications',
                'choose_another_model',
                'compatibility',
                'power_requirements',
                'warranty_info',
                'delivery_info',
                'payment_info',
                'faq_items',
            ],
            'pages' => ['seo_title', 'canonical_url', 'robots', 'og_title', 'og_description', 'og_image', 'faq_items'],
        ] as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                $existing = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn($tableName, $column)));

                if ($existing !== []) {
                    $table->dropColumn($existing);
                }
            });
        }
    }

    private function addSeoColumns(Blueprint $table, string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'seo_title')) {
            $table->string('seo_title', 180)->nullable();
        }

        if (! Schema::hasColumn($tableName, 'canonical_url')) {
            $table->string('canonical_url')->nullable();
        }

        if (! Schema::hasColumn($tableName, 'robots')) {
            $table->string('robots', 40)->nullable();
        }

        if (! Schema::hasColumn($tableName, 'og_title')) {
            $table->string('og_title', 180)->nullable();
        }

        if (! Schema::hasColumn($tableName, 'og_description')) {
            $table->string('og_description', 255)->nullable();
        }

        if (! Schema::hasColumn($tableName, 'og_image')) {
            $table->string('og_image')->nullable();
        }
    }
};
