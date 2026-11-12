<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'h1')) {
                $table->string('h1', 180)->nullable()->after('name');
            }

            if (! Schema::hasColumn('categories', 'focus_keyword')) {
                $table->string('focus_keyword', 180)->nullable()->after('h1');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 12, 2)->nullable()->change();
            }

            if (Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->change();
            }

            if (! Schema::hasColumn('products', 'h1')) {
                $table->string('h1', 180)->nullable()->after('name');
            }

            if (! Schema::hasColumn('products', 'focus_keyword')) {
                $table->string('focus_keyword', 180)->nullable()->after('h1');
            }
        });

        Schema::table('pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('pages', 'h1')) {
                $table->string('h1', 180)->nullable()->after('title');
            }

            if (! Schema::hasColumn('pages', 'focus_keyword')) {
                $table->string('focus_keyword', 180)->nullable()->after('h1');
            }

            if (! Schema::hasColumn('pages', 'status')) {
                $table->string('status', 40)->default('published')->after('type');
            }

            if (! Schema::hasColumn('pages', 'blog_category')) {
                $table->string('blog_category', 120)->nullable()->after('status');
            }
        });

        if (! Schema::hasTable('category_product')) {
            Schema::create('category_product', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['category_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');

        Schema::table('pages', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['h1', 'focus_keyword', 'status', 'blog_category'],
                fn (string $column): bool => Schema::hasColumn('pages', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['h1', 'focus_keyword'],
                fn (string $column): bool => Schema::hasColumn('products', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('categories', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['h1', 'focus_keyword'],
                fn (string $column): bool => Schema::hasColumn('categories', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
