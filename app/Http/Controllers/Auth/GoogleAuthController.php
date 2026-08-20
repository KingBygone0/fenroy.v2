<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $key = 'google-auth:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            abort(429, 'Too many authentication attempts.');
        }

        RateLimiter::hit($key, 60);

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in failed. Please try again.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $dirty = [];
            if (! $user->google_id) {
                $dirty['google_id'] = $googleUser->getId();
            }
            if (! $user->email_verified_at) {
                $dirty['email_verified_at'] = now();
            }
            if ($dirty) {
                $user->forceFill($dirty)->save();
                $user = $user->fresh();
            }
        } else {
            // forceCreate bypasses fillable so email_verified_at is set atomically
            // in a single INSERT — no extra save() needed, avoids event ordering issues.
            // Google CDN avatars are not stored in the local avatar column
            // (which expects a storage-relative path, not an external URL).
            $user = User::forceCreate([
                'name'              => strip_tags($googleUser->getName()),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'email_verified_at' => now(),
                'password'          => null,
            ]);
        }

        Log::info('google-auth: pre-login', [
            'user_id'        => $user->id,
            'email'          => $user->email,
            'verified'       => (string) $user->email_verified_at,
            'session_before' => session()->getId(),
        ]);

        Auth::login($user, remember: true);
        session()->save();

        Log::info('google-auth: post-login', [
            'auth_id'       => Auth::id(),
            'session_after' => session()->getId(),
            'intended'      => session()->get('url.intended', 'none'),
        ]);

        RateLimiter::clear($key);

        return redirect()->intended(route('account.profile'));
    }
}
