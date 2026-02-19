<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $posts = null;

        if ($request->filled('q')) {
            $posts = Post::published()
                ->search($request->q)
                ->with(['user', 'category', 'state', 'city'])
                ->withCount('comments')
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
        }

        return Inertia::render('search', [
            'posts' => $posts,
            'query' => $request->q,
        ]);
    }
}
