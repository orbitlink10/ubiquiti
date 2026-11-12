<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->string('role', 180)->default('Customer');
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $records = [];
        $timestamp = now();

        if (Schema::hasTable('homepage_contents') && Schema::hasColumn('homepage_contents', 'testimonial_items')) {
            $homepageContent = DB::table('homepage_contents')->where('site_key', 'default')->first();
            $items = $homepageContent ? json_decode((string) $homepageContent->testimonial_items, true) : null;

            if (is_array($items)) {
                foreach ($items as $index => $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $name = trim((string) ($item['name'] ?? ''));
                    $quote = trim((string) ($item['quote'] ?? ''));
                    $role = trim((string) ($item['role'] ?? 'Customer'));

                    if ($name === '' || $quote === '') {
                        continue;
                    }

                    $records[] = [
                        'name' => mb_substr($name, 0, 180),
                        'role' => $role !== '' ? mb_substr($role, 0, 180) : 'Customer',
                        'quote' => $quote,
                        'rating' => 5,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }
        }

        if ($records === []) {
            $records = [
                [
                    'name' => 'Joan K., Nairobi',
                    'role' => 'Customer',
                    'quote' => 'The installation team arrived on time, explained the ideal mounting position, and got us online the same day. The experience felt professional from start to finish.',
                    'rating' => 5,
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                [
                    'name' => 'Samuel O., Meru',
                    'role' => 'Customer',
                    'quote' => 'Our children now attend online classes without interruptions, and video meetings are finally stable. Starlink has made a visible difference in our day-to-day routine.',
                    'rating' => 5,
                    'sort_order' => 2,
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                [
                    'name' => 'Victor M., Rongai',
                    'role' => 'Customer',
                    'quote' => 'Uploads that used to take forever now finish quickly, which matters a lot for my content work. For creators working outside strong fiber zones, this is a serious upgrade.',
                    'rating' => 5,
                    'sort_order' => 3,
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            ];
        }

        if ($records !== []) {
            DB::table('testimonials')->insert($records);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
