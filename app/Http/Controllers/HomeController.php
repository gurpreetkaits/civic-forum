<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::published()
            ->with(['user', 'category', 'state', 'city'])
            ->withCount('comments');

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        $sort = $request->get('sort', 'newest');
        $query = match($sort) {
            'newest' => $query->latest('published_at'),
            'most-voted' => $query->orderByDesc('vote_count'),
            default => $query->orderByDesc('vote_count')->orderByDesc('published_at'), // trending
        };

        $posts = $query->paginate(15)->withQueryString();

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

        return Inertia::render('home', [
            'posts' => $posts,
            'filters' => $request->only(['category', 'state_id', 'sort']),
        ]);
    }
}
