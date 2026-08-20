<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $key = 'google-auth:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            abort(429, 'Too many authentication attempts.');
        }

        RateLimiter::hit($key, 60);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in failed. Please try again.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link Google ID to existing account if not already linked
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'name'              => strip_tags($googleUser->getName()),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password'          => null,
            ]);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        RateLimiter::clear($key);

        return redirect()->intended(route('account.profile'));
    }
}
