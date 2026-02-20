<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\VoteController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Locale switch - rate limited to prevent abuse
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/locale', function (Request $request) {
        $request->validate(['locale' => 'required|in:en,hi']);
        return back()->withCookie(cookie()->forever('locale', $request->locale));
    })->name('locale.update');
});

// Public routes - rate limited
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/states/{state:code}', [StateController::class, 'show'])->name('states.show');
    Route::get('/states/{state:code}/{city}', [CityController::class, 'show'])->name('cities.show');
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/users/{user:username}', [UserProfileController::class, 'show'])->name('users.show');
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
});

// Authenticated routes (must come before /posts/{slug} wildcard)
Route::middleware('auth')->group(function () {
    // Post creation - admin only
    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store')->middleware('throttle:create-post');
    });

    // Post editing/deleting - owner or admin (authorized via PostPolicy)
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/posts/{slug}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{slug}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{slug}', [PostController::class, 'destroy'])->name('posts.destroy');
    });

    // Votes
    Route::middleware('throttle:vote')->group(function () {
        Route::post('/votes', [VoteController::class, 'store'])->name('votes.store');
    });

    // Comments
    Route::middleware('throttle:create-comment')->group(function () {
        Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });

    // Image uploads
    Route::middleware('throttle:upload-image')->group(function () {
        Route::post('/uploads/images', [ImageUploadController::class, 'store'])->name('uploads.images');
    });

    // Profile settings (5 per minute)
    Route::middleware('throttle:5,1')->group(function () {
        Route::get('/settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/settings/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

// Public post show (wildcard - must be after /posts/create)
Route::get('/posts/{slug}', [PostController::class, 'show'])->name('posts.show');

// API routes - rate limited
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/api/states/{state}/cities', [App\Http\Controllers\Api\CityController::class, 'index']);
});

// Authenticated verification routes
Route::middleware('auth')->group(function () {
    Route::get('/verification/submit', function () {
        return inertia('verification/submit');
    })->name('verification.submit');

    Route::get('/api/verification/status', [VerificationController::class, 'status']);
    Route::post('/api/verification/submit', [VerificationController::class, 'submit']);
});

// Admin verification API routes (no frontend page - managed via API only)
Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/api/verification/requests', [VerificationController::class, 'index']);
    Route::get('/api/verification/requests/{verificationRequest}', [VerificationController::class, 'show']);
    Route::post('/api/verification/requests/{verificationRequest}/approve', [VerificationController::class, 'approve']);
    Route::post('/api/verification/requests/{verificationRequest}/reject', [VerificationController::class, 'reject']);
});

require __DIR__.'/auth.php';
