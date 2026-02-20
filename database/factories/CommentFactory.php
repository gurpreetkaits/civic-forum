<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'type' => 'discussion',
            'depth' => 0,
        ];
    }

    public function question(): static
    {
        return $this->state(['type' => 'question']);
    }

    public function solution(): static
    {
        return $this->state(['type' => 'solution']);
    }

    public function replyTo(Comment $parent): static
    {
        return $this->state([
            'post_id' => $parent->post_id,
            'parent_id' => $parent->id,
            'depth' => $parent->depth + 1,
            'type' => $parent->type === 'question' ? 'solution' : $parent->type,
        ]);
    }
}
