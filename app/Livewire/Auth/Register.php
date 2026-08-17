<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Register extends Component
{
    public string $name                  = '';
    public string $email                 = '';
    public string $password              = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        // Component-level rate limit — guards against Livewire wire: calls bypassing route throttle
        $key = 'register.ip:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Too many registration attempts. Please wait {$seconds} seconds.");
            return;
        }

        $this->validate(
            [
                'name'     => 'required|string|max:100',
                'email'    => 'required|email|max:254|unique:users,email',
                'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
            ],
            [
                'email.unique'       => 'Unable to create account with these details.',
                'password.min'       => 'Password must be at least 12 characters.',
                'password.letters'   => 'Password must contain at least one letter.',
                'password.mixed'     => 'Password must contain both uppercase and lowercase letters.',
                'password.numbers'   => 'Password must contain at least one number.',
            ]
        );

        RateLimiter::hit($key, 3600);

        $user = User::create([
            'name'     => strip_tags($this->name),
            'email'    => $this->email,
            'password' => $this->password,
        ]);

        Log::channel('single')->info('New account registered', [
            'user_id' => $user->id,
            'ip'      => request()->ip(),
        ]);

        Auth::login($user);
        session()->regenerate();

        session()->forget([
            'cart_items', 'cart_count', 'cart_total',
            'cart_discount', 'cart_coupon', 'pending_order',
        ]);

        $this->redirect(route('account.profile'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.auth', ['title' => 'Create Account — Fenroy']);
    }
}
