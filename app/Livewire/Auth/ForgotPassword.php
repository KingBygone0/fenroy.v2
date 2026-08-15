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

    public string $status  = '';
    public string $error   = '';
    public bool   $sent    = false;

    public function send(): void
    {
        $this->validate();
        $this->status = '';
        $this->error  = '';

        $key = 'password.reset:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $minutes = ceil(RateLimiter::availableIn($key) / 60);
            $this->error = "Too many requests. Try again in {$minutes} minute(s).";
            return;
        }
        RateLimiter::hit($key, 3600);

        $result = Password::sendResetLink(['email' => $this->email]);

        if ($result === Password::RESET_LINK_SENT) {
            $this->sent   = true;
            $this->status = 'We\'ve sent a password reset link to ' . $this->email . '. Check your inbox.';
        } else {
            // Don't reveal whether email exists — generic message
            $this->sent   = true;
            $this->status = 'If an account with that email exists, a reset link has been sent.';
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('components.layouts.auth', ['title' => 'Reset Password — Fenroy']);
    }
}
