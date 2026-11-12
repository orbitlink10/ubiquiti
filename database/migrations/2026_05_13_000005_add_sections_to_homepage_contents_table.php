<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            if (!Schema::hasColumn('homepage_contents', 'why_choose_title')) {
                $table->string('why_choose_title', 180)->nullable()->after('hero_image_path');
            }

            if (!Schema::hasColumn('homepage_contents', 'why_choose_intro')) {
                $table->text('why_choose_intro')->nullable()->after('why_choose_title');
            }

            if (!Schema::hasColumn('homepage_contents', 'why_choose_items')) {
                $table->longText('why_choose_items')->nullable()->after('why_choose_intro');
            }

            if (!Schema::hasColumn('homepage_contents', 'testimonials_badge')) {
                $table->string('testimonials_badge', 120)->nullable()->after('why_choose_items');
            }

            if (!Schema::hasColumn('homepage_contents', 'testimonials_title')) {
                $table->string('testimonials_title', 180)->nullable()->after('testimonials_badge');
            }

            if (!Schema::hasColumn('homepage_contents', 'testimonials_intro')) {
                $table->text('testimonials_intro')->nullable()->after('testimonials_title');
            }

            if (!Schema::hasColumn('homepage_contents', 'testimonial_items')) {
                $table->longText('testimonial_items')->nullable()->after('testimonials_intro');
            }

            if (!Schema::hasColumn('homepage_contents', 'faq_badge')) {
                $table->string('faq_badge', 120)->nullable()->after('testimonial_items');
            }

            if (!Schema::hasColumn('homepage_contents', 'faq_title')) {
                $table->string('faq_title', 180)->nullable()->after('faq_badge');
            }

            if (!Schema::hasColumn('homepage_contents', 'faq_intro')) {
                $table->text('faq_intro')->nullable()->after('faq_title');
            }

            if (!Schema::hasColumn('homepage_contents', 'faq_items')) {
                $table->longText('faq_items')->nullable()->after('faq_intro');
            }

            if (!Schema::hasColumn('homepage_contents', 'content_badge')) {
                $table->string('content_badge', 120)->nullable()->after('faq_items');
            }

            if (!Schema::hasColumn('homepage_contents', 'content_title')) {
                $table->string('content_title', 220)->nullable()->after('content_badge');
            }

            if (!Schema::hasColumn('homepage_contents', 'content_intro')) {
                $table->text('content_intro')->nullable()->after('content_title');
            }

            if (!Schema::hasColumn('homepage_contents', 'content_body')) {
                $table->longText('content_body')->nullable()->after('content_intro');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table): void {
            $columns = [
                'why_choose_title',
                'why_choose_intro',
                'why_choose_items',
                'testimonials_badge',
                'testimonials_title',
                'testimonials_intro',
                'testimonial_items',
                'faq_badge',
                'faq_title',
                'faq_intro',
                'faq_items',
                'content_badge',
                'content_title',
                'content_intro',
                'content_body',
            ];

            $existingColumns = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('homepage_contents', $column)));

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
