<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if ($request->has('popup')) {
            session(['google_auth_popup' => true]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse|View
    {
        $isPopup = session()->pull('google_auth_popup', false);

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
                $user->update(['google_id' => $googleUser->getId()]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'username' => $this->generateUniqueUsername($googleUser->getName(), $googleUser->getEmail()),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'password' => null,
                ]);
            }
        }

        Auth::login($user, remember: true);

        if ($isPopup) {
            return view('auth.google-callback');
        }

        return redirect('/');
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
