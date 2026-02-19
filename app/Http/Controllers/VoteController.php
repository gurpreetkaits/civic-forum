<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoteRequest;
use App\Models\Comment;
use App\Models\Post;

class VoteController extends Controller
{
    public function store(VoteRequest $request)
    {
        $modelClass = match ($request->votable_type) {
            'post' => Post::class,
            'comment' => Comment::class,
        };

        $votable = $modelClass::findOrFail($request->votable_id);

        $votable->vote($request->user()->id, $request->value);

        return back();
    }
}
