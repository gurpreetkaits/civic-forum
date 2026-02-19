<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, Category $category)
    {
        $posts = Post::published()
            ->where('category_id', $category->id)
            ->with(['user', 'category', 'state', 'city'])
            ->withCount('comments')
            ->orderByDesc('vote_count')
            ->orderByDesc('published_at')
            ->paginate(15)
            ->withQueryString();

        // Attach user votes if authenticated
        if ($user = $request->user()) {
            $postIds = $posts->pluck('id');
            $votes = $user->votes()
                ->where('votable_type', 'App\\Models\\Post')
                ->whereIn('votable_id', $postIds)
                ->pluck('value', 'votable_id');

            $posts->getCollection()->transform(function ($post) use ($votes) {
                $post->user_vote = $votes[$post->id] ?? null;
                return $post;
            });
        }

        return Inertia::render('categories/show', [
            'category' => $category,
            'posts' => $posts,
        ]);
    }
}
