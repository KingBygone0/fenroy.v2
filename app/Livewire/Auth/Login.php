<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Login extends Component
{
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required|min:6')]
    public string $password = '';

    public bool $remember = false;

    public string $error = '';

    public function login(): void
    {
        $this->validate();

        $key = 'login:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->error = "Too many login attempts. Please wait {$seconds} seconds.";
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->error = 'These credentials do not match our records.';
            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();
        $this->redirect(route('account.profile'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.auth', ['title' => 'Sign In — Fenroy']);
    }
}
