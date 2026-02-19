<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\State;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StateController extends Controller
{
    public function show(Request $request, State $state)
    {
        $posts = Post::published()
            ->where('state_id', $state->id)
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

        return Inertia::render('states/show', [
            'state' => $state->load('cities'),
            'posts' => $posts,
        ]);
    }
}
