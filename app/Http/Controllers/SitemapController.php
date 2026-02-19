<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\State;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()
            ->select(['slug', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get();

        $states = State::select(['code', 'updated_at'])->get();

        $categories = Category::select(['slug', 'updated_at'])->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Homepage
        $xml .= '<url>';
        $xml .= '<loc>' . url('/') . '</loc>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';

        // Posts
        foreach ($posts as $post) {
            $xml .= '<url>';
            $xml .= '<loc>' . url('/posts/' . $post->slug) . '</loc>';
            $xml .= '<lastmod>' . $post->updated_at->toW3cString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        // States
        foreach ($states as $state) {
            $xml .= '<url>';
            $xml .= '<loc>' . url('/states/' . $state->code) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        // Categories
        foreach ($categories as $category) {
            $xml .= '<url>';
            $xml .= '<loc>' . url('/categories/' . $category->slug) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
