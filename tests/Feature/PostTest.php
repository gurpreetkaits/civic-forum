<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(\App\Http\Middleware\BotDetection::class);
    }

    // ── Show ────────────────────────────────────────────────

    public function test_guest_can_view_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->get("/posts/{$post->slug}");

        $response->assertStatus(200);
    }

    public function test_show_returns_grouped_comments_and_counts(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        // Create one of each type
        $post->comments()->create(['user_id' => $user->id, 'body' => 'disc', 'type' => 'discussion']);
        $post->comments()->create(['user_id' => $user->id, 'body' => 'q', 'type' => 'question']);

        $response = $this->get("/posts/{$post->slug}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('posts/show')
            ->has('comments.discussion', 1)
            ->has('comments.question', 1)
            ->has('commentCounts')
            ->where('commentCounts.discussion', 1)
            ->where('commentCounts.question', 1)
        );
    }

    public function test_show_increments_view_count(): void
    {
        $post = Post::factory()->create(['view_count' => 0]);

        $this->get("/posts/{$post->slug}");

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'view_count' => 1]);
    }

    public function test_show_returns_404_for_missing_post(): void
    {
        $response = $this->get('/posts/nonexistent-slug');

        $response->assertStatus(404);
    }

    // ── Create / Store ──────────────────────────────────────

    public function test_guest_cannot_access_create_post_page(): void
    {
        $response = $this->get('/posts/create');

        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_create_post_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/posts/create');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_create_post_page(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get('/posts/create');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_post(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();
        $state = State::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'Test Post Title',
            'body' => 'Test body content',
            'category_id' => $category->id,
            'state_id' => $state->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post Title',
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_create_post(): void
    {
        $category = Category::factory()->create();
        $state = State::factory()->create();

        $response = $this->post('/posts', [
            'title' => 'Test',
            'body' => 'Body',
            'category_id' => $category->id,
            'state_id' => $state->id,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_create_post(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $category = Category::factory()->create();
        $state = State::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'Test',
            'body' => 'Body',
            'category_id' => $category->id,
            'state_id' => $state->id,
        ]);

        $response->assertStatus(403);
    }

    // ── Edit / Update ───────────────────────────────────────

    public function test_owner_can_edit_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/posts/{$post->slug}/edit");

        $response->assertStatus(200);
    }

    public function test_admin_can_edit_any_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create();

        $response = $this->actingAs($admin)->get("/posts/{$post->slug}/edit");

        $response->assertStatus(200);
    }

    public function test_other_user_cannot_edit_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->get("/posts/{$post->slug}/edit");

        $response->assertStatus(403);
    }

    public function test_owner_can_update_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/posts/{$post->slug}", [
            'title' => 'Updated Title',
            'body' => 'Updated body',
            'category_id' => $post->category_id,
            'state_id' => $post->state_id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated Title']);
    }

    public function test_other_user_cannot_update_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->put("/posts/{$post->slug}", [
            'title' => 'Hacked',
            'body' => 'Hacked body',
            'category_id' => $post->category_id,
            'state_id' => $post->state_id,
        ]);

        $response->assertStatus(403);
    }

    // ── Delete ──────────────────────────────────────────────

    public function test_owner_can_delete_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/posts/{$post->slug}");

        $response->assertRedirect(route('home'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_admin_can_delete_any_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create();

        $response = $this->actingAs($admin)->delete("/posts/{$post->slug}");

        $response->assertRedirect(route('home'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_other_user_cannot_delete_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->delete("/posts/{$post->slug}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_guest_cannot_delete_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->delete("/posts/{$post->slug}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}
