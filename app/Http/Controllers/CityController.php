<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Post;
use App\Models\State;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityController extends Controller
{
    public function show(Request $request, State $state, string $citySlug)
    {
        $cityName = urldecode($citySlug);

        $city = City::where('state_id', $state->id)
            ->where('name', $cityName)
            ->firstOrFail();

        $posts = Post::published()
            ->where('city_id', $city->id)
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

        return Inertia::render('cities/show', [
            'state' => $state,
            'city' => $city,
            'posts' => $posts,
        ]);
    }
}
