<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $response = Socialite::driver('google')->redirect();

        // Set a cookie to track popup mode (will persist through redirects)
        if ($request->has('popup')) {
            $response->withCookie(cookie('google_auth_popup', '1', 10)); // 10 minutes
        }

        return $response;
    }

    public function callback(Request $request): RedirectResponse|Response|View
    {
        $isPopup = $request->cookie('google_auth_popup') === '1';

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            if ($isPopup) {
                return view('auth.google-callback-error');
            }

            return redirect('/login')->with('error', 'Google authentication was cancelled or failed.');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar_path' => $user->avatar_path ?: $googleUser->getAvatar(),
                    'name' => $user->name ?: $googleUser->getName(),
                ]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'username' => $this->generateUniqueUsername($googleUser->getName(), $googleUser->getEmail()),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar_path' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => null,
                ]);
            }
        } else {
            // Update avatar and name from Google if not set locally
            $updates = [];
            if (! $user->avatar_path || str_starts_with($user->avatar_path, 'http')) {
                $updates['avatar_path'] = $googleUser->getAvatar();
            }
            if (! $user->name || $user->name === $user->username) {
                $updates['name'] = $googleUser->getName();
            }
            if ($updates) {
                $user->update($updates);
            }
        }

        Auth::login($user, remember: true);

        if ($isPopup) {
            // Clear the popup cookie and return the callback view
            return response()
                ->view('auth.google-callback')
                ->withCookie(cookie()->forget('google_auth_popup'));
        }

        return redirect('/')->withCookie(cookie()->forget('google_auth_popup'));
    }

    private function generateUniqueUsername(string $name, string $email): string
    {
        $base = Str::slug($name, '_');

        if (empty($base)) {
            $base = Str::before($email, '@');
        }

        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }
}
