<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    public function creating(Post $post): void
    {
        if (empty($post->slug)) {
            $slug = Str::slug($post->title);
            $original = $slug;
            $count = 1;

            while (Post::where('slug', $slug)->exists()) {
                $slug = $original . '-' . $count++;
            }

            $post->slug = $slug;
        }

        if (empty($post->published_at) && $post->status === 'published') {
            $post->published_at = now();
        }
    }
}
