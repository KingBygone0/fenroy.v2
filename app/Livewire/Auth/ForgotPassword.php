<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ForgotPassword extends Component
{
    #[Rule('required|email')]
    public string $email = '';

    public string $status = '';
    public string $error  = '';
    public bool   $sent   = false;

    // The same message is shown regardless of whether the account exists.
    // Showing a different message when the email IS found leaks account existence.
    private const GENERIC_SENT_MSG = 'If an account with that email exists, a reset link has been sent.';

    public function send(): void
    {
        $this->validate();
        $this->status = '';
        $this->error  = '';

        $key = 'password.reset:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            $this->error = "Too many requests. Try again in {$minutes} minute(s).";
            return;
        }
        RateLimiter::hit($key, 3600);

        // Always call sendResetLink (so timing is consistent) and always show the same message.
        Password::sendResetLink(['email' => $this->email]);

        $this->sent   = true;
        $this->status = self::GENERIC_SENT_MSG;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('components.layouts.auth', ['title' => 'Reset Password — Fenroy']);
    }
}
