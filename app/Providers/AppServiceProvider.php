<?php

namespace App\Providers;

use App\Models\Post;
use App\Observers\PostObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Post::observe(PostObserver::class);

        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiting for the application.
     */
    protected function configureRateLimiting(): void
    {
        // One post per day limit
        RateLimiter::for('create-post', function (Request $request) {
            $user = $request->user();
            $key = $user ? 'user-' . $user->id : 'ip-' . $request->ip();

            return Limit::perDay(1)->by($key)->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'You have already created a post today. Please try again tomorrow.',
                    'next_post_allowed' => now()->addDay()->toISOString(),
                ], 429, $headers);
            });
        });

        // Custom rate limit messages for different actions
        RateLimiter::for('create-comment', function (Request $request) {
            return Limit::perMinute(10)->by('user-' . $request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'You are posting comments too quickly. Please wait a moment before commenting again.',
                    ], 429, $headers);
                });
        });

        RateLimiter::for('upload-image', function (Request $request) {
            return Limit::perMinute(20)->by('user-' . $request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'You are uploading too many images. Please wait before uploading more.',
                    ], 429, $headers);
                });
        });

        RateLimiter::for('vote', function (Request $request) {
            return Limit::perMinute(60)->by('user-' . $request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'You are voting too quickly. Please wait a moment.',
                    ], 429, $headers);
                });
        });
    }
}
