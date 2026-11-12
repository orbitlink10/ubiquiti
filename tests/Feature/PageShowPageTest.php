<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_renders_story_layout_with_feature_image_and_rich_content(): void
    {
        $page = Page::create([
            'meta_title' => 'Starlink in Kenya: The Powerful Internet Revolution Transforming Connectivity',
            'meta_description' => 'Discover the benefits of Starlink in Kenya and how satellite internet is improving connectivity for homes, schools, and businesses.',
            'title' => 'Starlink in Kenya',
            'heading_two' => 'Connectivity Guide',
            'slug' => 'starlink-in-kenya',
            'image_url' => 'https://example.com/images/starlink-kenya.jpg',
            'alt_text' => 'Starlink terminal in Kenya',
            'type' => 'post',
            'body' => '<p>Reliable rural connectivity.</p><script>alert(1)</script><p>Deployment options across Kenya.</p>',
        ]);

        $this->assertSame('http://localhost/starlink-in-kenya', route('pages.show', ['page' => $page->slug]));

        $response = $this->get(route('pages.show', ['page' => $page->slug]));

        $response->assertOk();
        $response->assertSee('Starlink in Kenya');
        $response->assertSee('Connectivity Guide');
        $response->assertSee('Discover the benefits of Starlink in Kenya and how satellite internet is improving connectivity for homes, schools, and businesses.');
        $response->assertSee('Starlink in Kenya: The Powerful Internet Revolution Transforming Connectivity');
        $response->assertSee('Reliable rural connectivity.');
        $response->assertSee('Deployment options across Kenya.');
        $response->assertSee('Shop Products');
        $response->assertSee('Read Article');
        $response->assertSee('Back');
        $response->assertSee('https://example.com/images/starlink-kenya.jpg', false);
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_legacy_pages_url_redirects_to_root_page_slug(): void
    {
        Page::create([
            'meta_title' => 'Starlink in Kenya',
            'meta_description' => 'Discover the benefits of Starlink in Kenya.',
            'title' => 'Starlink in Kenya',
            'heading_two' => 'Connectivity Guide',
            'slug' => 'starlink-in-kenya',
            'type' => 'post',
            'body' => '<p>Reliable rural connectivity.</p>',
        ]);

        $this->get('/pages/starlink-in-kenya')
            ->assertStatus(301)
            ->assertRedirect('/starlink-in-kenya');
    }
}
