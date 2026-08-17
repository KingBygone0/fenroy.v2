<?php

namespace App\Livewire\Pos;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Login extends Component
{
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public string $error = '';

    public function mount(): void
    {
        if (Auth::check() && (Auth::user()->is_admin || Auth::user()->is_staff)) {
            $this->redirectRoute('pos.terminal');
        }
    }

    public function login(): void
    {
        $key = 'pos.login:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Too many attempts. Please wait a minute.';
            return;
        }

        $this->validate();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], false)) {
            RateLimiter::hit($key, 60);
            $this->error = 'Invalid email or password.';
            return;
        }

        $user = Auth::user();

        if (! $user->is_admin && ! $user->is_staff) {
            Auth::logout();
            $this->error = 'Your account does not have POS access.';
            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();
        $this->redirectRoute('pos.terminal');
    }

    public function render()
    {
        return view('livewire.pos.login')
            ->layout('layouts.pos-auth');
    }
}
