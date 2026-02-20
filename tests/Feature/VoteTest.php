<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(\App\Http\Middleware\BotDetection::class);
    }

    // ── Authentication ──────────────────────────────────────

    public function test_guest_cannot_vote(): void
    {
        $post = Post::factory()->create();

        $response = $this->post('/votes', [
            'votable_type' => 'post',
            'votable_id' => $post->id,
            'value' => 1,
        ]);

        $response->assertRedirect('/login');
    }

    // ── Post voting ─────────────────────────────────────────

    public function test_user_can_upvote_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post('/votes', [
            'votable_type' => 'post',
            'votable_id' => $post->id,
            'value' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('votes', [
            'user_id' => $user->id,
            'votable_type' => 'App\\Models\\Post',
            'votable_id' => $post->id,
            'value' => 1,
        ]);
        $this->assertEquals(1, $post->fresh()->vote_count);
    }

    public function test_user_can_downvote_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->post('/votes', [
            'votable_type' => 'post',
            'votable_id' => $post->id,
            'value' => -1,
        ]);

        $this->assertDatabaseHas('votes', [
            'user_id' => $user->id,
            'votable_id' => $post->id,
            'value' => -1,
        ]);
        $this->assertEquals(-1, $post->fresh()->vote_count);
    }

    public function test_voting_same_value_removes_vote(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        // Vote up
        $this->actingAs($user)->post('/votes', [
            'votable_type' => 'post',
            'votable_id' => $post->id,
            'value' => 1,
        ]);
        $this->assertEquals(1, $post->fresh()->vote_count);

        // Vote up again → removes vote
        $this->actingAs($user)->post('/votes', [
            'votable_type' => 'post',
            'votable_id' => $post->id,
            'value' => 1,
        ]);
        $this->assertEquals(0, $post->fresh()->vote_count);
    }

    public function test_voting_opposite_value_changes_vote(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        // Vote up
        $this->actingAs($user)->post('/votes', [
            'votable_type' => 'post',
            'votable_id' => $post->id,
            'value' => 1,
        ]);
        $this->assertEquals(1, $post->fresh()->vote_count);

        // Vote down → changes from +1 to -1
        $this->actingAs($user)->post('/votes', [
            'votable_type' => 'post',
            'votable_id' => $post->id,
            'value' => -1,
        ]);
        $this->assertEquals(-1, $post->fresh()->vote_count);
    }

    // ── Comment voting ──────────────────────────────────────

    public function test_user_can_upvote_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)->post('/votes', [
            'votable_type' => 'comment',
            'votable_id' => $comment->id,
            'value' => 1,
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, $comment->fresh()->vote_count);
    }

    public function test_user_can_downvote_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $this->actingAs($user)->post('/votes', [
            'votable_type' => 'comment',
            'votable_id' => $comment->id,
            'value' => -1,
        ]);

        $this->assertEquals(-1, $comment->fresh()->vote_count);
    }

    // ── Validation ──────────────────────────────────────────

    public function test_votable_type_must_be_valid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/votes', [
            'votable_type' => 'invalid',
            'votable_id' => 1,
            'value' => 1,
        ]);

        $response->assertSessionHasErrors('votable_type');
    }

    public function test_value_must_be_1_or_negative_1(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post('/votes', [
            'votable_type' => 'post',
            'votable_id' => $post->id,
            'value' => 5,
        ]);

        $response->assertSessionHasErrors('value');
    }

    // ── User votes attached on post show ────────────────────

    public function test_show_attaches_user_vote_to_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $post->vote($user->id, 1);

        $response = $this->actingAs($user)->get("/posts/{$post->slug}");

        $response->assertInertia(fn ($page) => $page
            ->where('post.user_vote', 1)
        );
    }

    public function test_show_attaches_user_votes_to_comments(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'type' => 'discussion',
            'depth' => 0,
            'parent_id' => null,
        ]);
        $comment->vote($user->id, -1);

        $response = $this->actingAs($user)->get("/posts/{$post->slug}");

        $response->assertInertia(fn ($page) => $page
            ->where('comments.discussion.0.user_vote', -1)
        );
    }
}
