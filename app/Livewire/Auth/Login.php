<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Login extends Component
{
    #[Rule('required|email')]
    public string $email = '';

    // Only `required` — password length is irrelevant at auth time (the hash does the real check)
    #[Rule('required')]
    public string $password = '';

    public bool $remember = false;

    public string $error = '';

    public function login(): void
    {
        $this->validate();

        // Dual key rate limiting — blocks both per-IP spraying and per-email stuffing
        $ipKey    = 'login.ip:'    . request()->ip();
        $emailKey = 'login.email:' . Str::lower(trim($this->email));

        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $this->error = "Too many login attempts. Please wait {$seconds} seconds.";
            return;
        }

        if (RateLimiter::tooManyAttempts($emailKey, 10)) {
            $seconds = RateLimiter::availableIn($emailKey);
            $this->error = "Too many login attempts for this account. Please wait {$seconds} seconds.";
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Increment both counters on failure
            RateLimiter::hit($ipKey,    60);   // 5 per minute per IP
            RateLimiter::hit($emailKey, 900);  // 10 per 15 minutes per email

            Log::channel('single')->warning('Failed login attempt', [
                'email' => $this->email,
                'ip'    => request()->ip(),
            ]);

            $this->error = 'These credentials do not match our records.';
            return;
        }

        RateLimiter::clear($ipKey);
        RateLimiter::clear($emailKey);

        session()->regenerate();

        Log::channel('single')->info('User logged in', [
            'user_id' => Auth::id(),
            'ip'      => request()->ip(),
        ]);

        $this->redirect(route('account.profile'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.auth', ['title' => 'Sign In — Fenroy']);
    }
}
