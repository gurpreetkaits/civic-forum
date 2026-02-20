<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(\App\Http\Middleware\BotDetection::class);
    }

    // ── Store — authentication ──────────────────────────────

    public function test_guest_cannot_create_comment(): void
    {
        $post = Post::factory()->create();

        $response = $this->post("/posts/{$post->id}/comments", [
            'body' => 'Hello',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_user_can_create_discussion_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => 'Great discussion point',
            'type' => 'discussion',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Great discussion point',
            'type' => 'discussion',
            'depth' => 0,
        ]);
    }

    public function test_user_can_create_question_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => 'How do we fix this?',
            'type' => 'question',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'body' => 'How do we fix this?',
            'type' => 'question',
        ]);
    }

    public function test_comment_defaults_to_discussion_type(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => 'No type specified',
        ]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'body' => 'No type specified',
            'type' => 'discussion',
        ]);
    }

    public function test_invalid_type_is_rejected(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => 'Bad type',
            'type' => 'invalid',
        ]);

        $response->assertSessionHasErrors('type');
    }

    // ── Store — replies & type inheritance ───────────────────

    public function test_reply_to_discussion_inherits_discussion_type(): void
    {
        $user = User::factory()->create();
        $parent = Comment::factory()->create(['type' => 'discussion']);

        $this->actingAs($user)->post("/posts/{$parent->post_id}/comments", [
            'body' => 'Reply to discussion',
            'parent_id' => $parent->id,
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'Reply to discussion',
            'parent_id' => $parent->id,
            'type' => 'discussion',
            'depth' => 1,
        ]);
    }

    public function test_reply_to_question_becomes_solution(): void
    {
        $user = User::factory()->create();
        $question = Comment::factory()->question()->create();

        $this->actingAs($user)->post("/posts/{$question->post_id}/comments", [
            'body' => 'Here is the solution',
            'parent_id' => $question->id,
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'Here is the solution',
            'parent_id' => $question->id,
            'type' => 'solution',
            'depth' => 1,
        ]);
    }

    public function test_reply_to_solution_inherits_solution_type(): void
    {
        $user = User::factory()->create();
        $question = Comment::factory()->question()->create();
        $solution = Comment::factory()->replyTo($question)->create();

        $this->actingAs($user)->post("/posts/{$solution->post_id}/comments", [
            'body' => 'Great solution!',
            'parent_id' => $solution->id,
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'Great solution!',
            'parent_id' => $solution->id,
            'type' => 'solution',
            'depth' => 2,
        ]);
    }

    public function test_reply_type_from_request_is_ignored_in_favor_of_parent(): void
    {
        $user = User::factory()->create();
        $question = Comment::factory()->question()->create();

        // User sends type=discussion, but parent is question → should become solution
        $this->actingAs($user)->post("/posts/{$question->post_id}/comments", [
            'body' => 'Attempt to override type',
            'parent_id' => $question->id,
            'type' => 'discussion',
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'Attempt to override type',
            'type' => 'solution',
        ]);
    }

    // ── Store — depth calculation ───────────────────────────

    public function test_top_level_comment_has_depth_zero(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => 'Top level',
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'Top level',
            'depth' => 0,
            'parent_id' => null,
        ]);
    }

    public function test_nested_reply_increments_depth(): void
    {
        $user = User::factory()->create();
        $parent = Comment::factory()->create(['depth' => 2]);

        $this->actingAs($user)->post("/posts/{$parent->post_id}/comments", [
            'body' => 'Deep reply',
            'parent_id' => $parent->id,
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'Deep reply',
            'depth' => 3,
        ]);
    }

    // ── Store — comment_count increment ─────────────────────

    public function test_creating_comment_increments_post_comment_count(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['comment_count' => 0]);

        $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => 'A comment',
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'comment_count' => 1,
        ]);
    }

    // ── Store — validation ──────────────────────────────────

    public function test_comment_body_is_required(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => '',
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_comment_body_max_length(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_parent_id_must_exist(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'body' => 'Orphan reply',
            'parent_id' => 999999,
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    // ── Update ──────────────────────────────────────────────

    public function test_owner_can_update_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/comments/{$comment->id}", [
            'body' => 'Updated body',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Updated body',
        ]);
    }

    public function test_other_user_cannot_update_comment(): void
    {
        $other = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($other)->put("/comments/{$comment->id}", [
            'body' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_update_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->put("/comments/{$comment->id}", [
            'body' => 'Hacked',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_update_validates_body(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/comments/{$comment->id}", [
            'body' => '',
        ]);

        $response->assertSessionHasErrors('body');
    }

    // ── Delete ──────────────────────────────────────────────

    public function test_owner_can_delete_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['comment_count' => 1]);
        $comment = Comment::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $response = $this->actingAs($user)->delete("/comments/{$comment->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'comment_count' => 0]);
    }

    public function test_other_user_cannot_delete_comment(): void
    {
        $other = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($other)->delete("/comments/{$comment->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_guest_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->delete("/comments/{$comment->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_deleting_comment_decrements_post_comment_count(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['comment_count' => 3]);
        $comment = Comment::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $this->actingAs($user)->delete("/comments/{$comment->id}");

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'comment_count' => 2]);
    }
}
