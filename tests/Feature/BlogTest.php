<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $attributes = []): Post
    {
        return Post::create([
            'type' => 'article',
            'title' => 'Hello blog',
            'body' => '<p>First post.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
            ...$attributes,
        ]);
    }

    public function test_only_live_posts_are_public(): void
    {
        $live = $this->makePost();
        $draft = $this->makePost(['title' => 'Draft post', 'status' => 'draft']);
        $scheduled = $this->makePost(['title' => 'Later post', 'published_at' => now()->addWeek()]);

        $this->get('/')->assertOk()->assertSee('Hello blog')->assertDontSee('Draft post')->assertDontSee('Later post');
        $this->get('/blog/'.$live->slug)->assertOk()->assertSee('First post.');
        $this->get('/blog/'.$draft->slug)->assertNotFound();
        $this->get('/blog/'.$scheduled->slug)->assertNotFound();
    }

    public function test_feed_sitemap_and_robots_are_served(): void
    {
        $post = $this->makePost();

        $this->get('/feed.xml')->assertOk()->assertSee('Hello blog');
        $this->get('/sitemap.xml')->assertOk()->assertSee('/blog/'.$post->slug);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:');
    }

    public function test_dashboard_needs_a_login_and_editors_can_write(): void
    {
        $this->get('/dashboard')->assertRedirect('/dashboard/login');

        $editor = User::create(['name' => 'Ed', 'email' => 'ed@example.com', 'password' => 'a-long-test-password', 'role' => 'editor']);
        $this->actingAs($editor)->post('/dashboard/posts', [
            'title' => 'Written in the dashboard',
            'type' => 'article',
            'status' => 'published',
            'body' => '<p>Body</p><script>alert(1)</script>',
        ])->assertRedirect();

        $post = Post::firstWhere('title', 'Written in the dashboard');
        $this->assertNotNull($post);
        $this->assertStringNotContainsString('<script', $post->body);
    }

    public function test_viewers_cannot_write(): void
    {
        $viewer = User::create(['name' => 'Vi', 'email' => 'vi@example.com', 'password' => 'a-long-test-password', 'role' => 'viewer']);

        $this->actingAs($viewer)->get('/dashboard')->assertOk();
        $this->actingAs($viewer)->get('/dashboard/posts/create')->assertForbidden();
    }

    public function test_under_construction_shows_a_holding_page_to_visitors_only(): void
    {
        $this->makePost();
        $admin = User::create(['name' => 'Ad', 'email' => 'ad@example.com', 'password' => 'a-long-test-password', 'role' => 'admin']);

        $this->actingAs($admin)->put('/dashboard/settings', ['under_construction' => '1'])->assertRedirect('/dashboard/settings');
        auth()->logout();

        $this->get('/')->assertStatus(503)->assertSee('under construction');
        $this->get('/blog/hello-blog')->assertStatus(503);
        $this->get('/dashboard/login')->assertOk();

        $this->actingAs($admin)->get('/')->assertOk()->assertSee('Hello blog');

        $this->actingAs($admin)->put('/dashboard/settings', [])->assertRedirect();
        auth()->logout();
        $this->get('/')->assertOk();
    }

    public function test_only_admins_can_open_settings(): void
    {
        $editor = User::create(['name' => 'Ed', 'email' => 'ed@example.com', 'password' => 'a-long-test-password', 'role' => 'editor']);

        $this->actingAs($editor)->get('/dashboard/settings')->assertForbidden();
        $this->actingAs($editor)->put('/dashboard/settings', ['under_construction' => '1'])->assertForbidden();
    }
}
