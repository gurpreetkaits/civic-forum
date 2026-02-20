<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post)
    {
        $depth = 0;
        $type = $request->input('type', 'discussion');

        if ($request->filled('parent_id')) {
            $parent = Comment::findOrFail($request->parent_id);
            $depth = $parent->depth + 1;
            // Replies to questions become solutions; otherwise inherit parent type
            $type = $parent->type === 'question' ? 'solution' : $parent->type;
        }

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $request->parent_id,
            'body' => $request->body,
            'type' => $type,
            'depth' => $depth,
        ]);

        $post->increment('comment_count');

        return back();
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment->update([
            'body' => $request->body,
        ]);

        return back();
    }

    public function destroy(Request $request, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $post = $comment->post;

        $comment->delete();

        $post->decrement('comment_count');

        return back();
    }
}
