<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\State;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $post = Post::where('slug', $slug)
            ->with(['user', 'category', 'state', 'city', 'images', 'tags'])
            ->firstOrFail();

        $discussionEagerLoads = [
            'user',
            'replies.user',
            'replies.replies.user',
            'replies.replies.replies.user',
        ];

        // Questions eager-load solutions (replies) sorted by votes so best solutions rise
        $questionEagerLoads = [
            'user',
            'replies' => fn ($q) => $q->orderByDesc('vote_count'),
            'replies.user',
            'replies.replies' => fn ($q) => $q->orderByDesc('vote_count'),
            'replies.replies.user',
            'replies.replies.replies.user',
        ];

        $baseQuery = fn () => $post->comments()
            ->whereNull('parent_id')
            ->where('depth', 0);

        // Load comments grouped by type with type-specific sorting
        $grouped = [
            'discussion' => $baseQuery()->where('type', 'discussion')
                ->with($discussionEagerLoads)
                ->orderByDesc('vote_count')->get(),
            'question'   => $baseQuery()->where('type', 'question')
                ->with($questionEagerLoads)
                ->orderByDesc('created_at')->get(),
        ];

        $commentCounts = [
            'discussion' => $grouped['discussion']->count(),
            'question'   => $grouped['question']->count(),
        ];

        // Increment view count
        $post->increment('view_count');

        // Attach user votes if authenticated
        if ($user = $request->user()) {
            // Vote on the post itself
            $post->user_vote = $user->votes()
                ->where('votable_type', 'App\\Models\\Post')
                ->where('votable_id', $post->id)
                ->value('value');

            // Collect all comment IDs across all groups
            $allCommentIds = [];
            foreach ($grouped as $comments) {
                $allCommentIds = array_merge($allCommentIds, $this->collectCommentIds($comments));
            }

            if (!empty($allCommentIds)) {
                $commentVotes = $user->votes()
                    ->where('votable_type', 'App\\Models\\Comment')
                    ->whereIn('votable_id', $allCommentIds)
                    ->pluck('value', 'votable_id');

                foreach ($grouped as $comments) {
                    $this->attachVotesToComments($comments, $commentVotes);
                }
            }
        }

        return Inertia::render('posts/show', [
            'post' => $post,
            'comments' => $grouped,
            'commentCounts' => $commentCounts,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $limit = $user?->is_verified ? 5 : 1;
        $key = $user ? 'user-' . $user->id : 'ip-' . $request->ip();

        // Check if user has hit their rate limit
        $rateLimiter = app(\Illuminate\Cache\RateLimiter::class);
        $maxAttempts = $limit;

        if ($rateLimiter->tooManyAttempts('create-post:' . $key, $maxAttempts)) {
            $availableAt = $rateLimiter->availableIn('create-post:' . $key);

            return Inertia::render('posts/create-limit-reached', [
                'limit' => $limit,
                'next_post_allowed' => now()->addSeconds($availableAt)->toISOString(),
                'is_verified' => $user?->is_verified ?? false,
            ]);
        }

        return Inertia::render('posts/create', [
            'categories' => Category::orderBy('sort_order')->get(),
            'states' => State::orderBy('name')->get(),
        ]);
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'body' => $request->body,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $path = $image->store("posts/{$post->id}", 'public');
                $post->images()->create([
                    'image_path' => $path,
                    'sort_order' => $i,
                ]);
            }
        }

        // Handle tags (can be comma-separated string or array)
        if ($request->filled('tags')) {
            $tagNames = is_string($request->tags) ? explode(',', $request->tags) : $request->tags;
            $tagIds = collect($tagNames)->filter()->map(function ($tagName) {
                $tagName = trim($tagName);
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                )->id;
            });
            $post->tags()->sync($tagIds);
        }

        return redirect()->route('posts.show', $post->slug);
    }

    public function edit(Request $request, string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $post);

        return Inertia::render('posts/edit', [
            'post' => $post->load(['tags', 'images']),
        ]);
    }

    public function update(UpdatePostRequest $request, string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $post);

        $post->update([
            'title' => $request->title,
            'body' => $request->body,
            'category_id' => $request->category_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
        ]);

        // Sync tags
        if ($request->has('tags')) {
            $tagNames = is_string($request->tags) ? explode(',', $request->tags) : $request->tags;
            $tagIds = collect($tagNames)->filter()->map(function ($tagName) {
                $tagName = trim($tagName);
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                )->id;
            });
            $post->tags()->sync($tagIds);
        }

        return redirect()->route('posts.show', $post->slug);
    }

    public function destroy(Request $request, string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('home');
    }

    /**
     * Recursively collect all comment IDs from nested comments.
     */
    private function collectCommentIds($comments): array
    {
        $ids = [];
        foreach ($comments as $comment) {
            $ids[] = $comment->id;
            if ($comment->relationLoaded('replies') && $comment->replies->isNotEmpty()) {
                $ids = array_merge($ids, $this->collectCommentIds($comment->replies));
            }
        }
        return $ids;
    }

    /**
     * Recursively attach user votes to comments.
     */
    private function attachVotesToComments($comments, $votes): void
    {
        foreach ($comments as $comment) {
            $comment->user_vote = $votes[$comment->id] ?? null;
            if ($comment->relationLoaded('replies') && $comment->replies->isNotEmpty()) {
                $this->attachVotesToComments($comment->replies, $votes);
            }
        }
    }
}
