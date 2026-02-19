<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    public function show(Request $request, User $user)
    {
        $user->load(['state', 'city']);

        $posts = $user->posts()
            ->published()
            ->with(['category', 'state', 'city'])
            ->withCount('comments')
            ->orderByDesc('published_at')
            ->paginate(15)
            ->withQueryString();

        // Attach user votes if authenticated
        if ($authUser = $request->user()) {
            $postIds = $posts->pluck('id');
            $votes = $authUser->votes()
                ->where('votable_type', 'App\\Models\\Post')
                ->whereIn('votable_id', $postIds)
                ->pluck('value', 'votable_id');

            $posts->getCollection()->transform(function ($post) use ($votes) {
                $post->user_vote = $votes[$post->id] ?? null;
                return $post;
            });
        }

        $comments = $user->comments()
            ->with(['post:id,title,slug'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'comments_page')
            ->withQueryString();

        return Inertia::render('users/show', [
            'profileUser' => $user,
            'posts' => $posts,
            'comments' => $comments,
        ]);
    }
}
