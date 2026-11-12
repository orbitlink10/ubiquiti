<?php

namespace Tests\Feature;

use App\Models\HomepageContent;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_navigation_does_not_auto_render_latest_pages(): void
    {
        $olderPage = $this->createPage('Older Navigation Article', 'older-navigation-article');
        $olderPage->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->save();

        $newerPage = $this->createPage('Newest Navigation Article', 'newest-navigation-article');
        $newerPage->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('class="primary-nav"', false);
        $response->assertDontSee('Newest Navigation Article');
        $response->assertDontSee('Older Navigation Article');
    }

    public function test_homepage_content_menu_renders_dashboard_controlled_navbar(): void
    {
        $this->createPage('Fallback Navigation Article', 'fallback-navigation-article');

        HomepageContent::create([
            'site_key' => HomepageContent::DEFAULT_SITE_KEY,
            'hero_title' => 'MikroTik Kenya',
            'hero_description' => 'Networking equipment for Kenya.',
            'nav_menu_items' => [
                ['label' => 'Support Desk', 'url' => '/support'],
                ['label' => 'Router Deals', 'url' => '/category/mikrotik-router-prices-in-kenya'],
            ],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('class="primary-nav"', false);
        $response->assertSee('<a class="primary-nav-link" href="' . route('home') . '">Home</a>', false);
        $response->assertSeeInOrder(['Home', 'Support Desk', 'Router Deals']);
        $response->assertSee('Support Desk');
        $response->assertSee('/category/mikrotik-router-prices-in-kenya', false);
        $response->assertDontSee('Fallback Navigation Article');
    }

    public function test_admin_homepage_content_save_controls_nav_menu_items(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $defaultHeroTitle = HomepageContent::current()->hero_title;

        $this->actingAs($admin)
            ->post(route('admin.pages-content.update'), [
                'section' => 'navigation',
                'nav_menu_items' => [
                    ['label' => 'Routers', 'url' => 'category/mikrotik-router-prices-in-kenya'],
                    ['label' => 'Contact', 'url' => '/contact-us'],
                    ['label' => '', 'url' => '/blank-label'],
                    ['label' => 'Unsafe', 'url' => 'javascript:alert(1)'],
                ],
            ])
            ->assertRedirect(route('admin.pages-content.edit'));

        $homepageContent = HomepageContent::query()
            ->where('site_key', HomepageContent::DEFAULT_SITE_KEY)
            ->firstOrFail();

        $this->assertSame($defaultHeroTitle, $homepageContent->hero_title);
        $this->assertSame([
            ['label' => 'Routers', 'url' => '/category/mikrotik-router-prices-in-kenya'],
            ['label' => 'Contact', 'url' => '/contact-us'],
        ], $homepageContent->navMenuItems());
    }

    public function test_admin_pages_index_lists_recently_published_pages_first(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $olderPage = $this->createPage('Older Admin Page', 'older-admin-page');
        $olderPage->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->save();

        $newerPage = $this->createPage('Newest Admin Page', 'newest-admin-page');
        $newerPage->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.pages.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'Newest Admin Page',
                'Older Admin Page',
            ]);
    }

    public function test_admin_pages_index_paginates_after_twenty_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        for ($pageNumber = 1; $pageNumber <= 21; $pageNumber++) {
            $page = $this->createPage(
                sprintf('Paginated Admin Page %02d', $pageNumber),
                sprintf('paginated-admin-page-%02d', $pageNumber)
            );

            $page->forceFill([
                'created_at' => now()->subMinutes($pageNumber),
                'updated_at' => now()->subMinutes($pageNumber),
            ])->save();
        }

        $this->actingAs($admin)
            ->get(route('admin.pages.index'))
            ->assertOk()
            ->assertSee('Page 1 of 2')
            ->assertSee('Next')
            ->assertSee('Paginated Admin Page 01')
            ->assertDontSee('Paginated Admin Page 21');

        $this->actingAs($admin)
            ->get(route('admin.pages.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Page 2 of 2')
            ->assertSee('Previous')
            ->assertSee('Paginated Admin Page 21');
    }

    private function createPage(string $title, string $slug): Page
    {
        return Page::create([
            'meta_title' => $title,
            'meta_description' => 'Navigation test page content.',
            'title' => $title,
            'heading_two' => $title,
            'slug' => $slug,
            'type' => 'post',
            'body' => '<p>Navigation page body.</p>',
        ]);
    }
}
