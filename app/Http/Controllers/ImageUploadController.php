<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('image')->store('posts/temp', 'public');

        return response()->json([
            'path' => $path,
        ]);
    }
}
