<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VerifyEmail extends Component
{
    public bool $sent = false;

    public function resend(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirect(route('account.profile'), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();
        $this->sent = true;
    }

    public function render()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect(route('account.profile'));
        }

        return view('livewire.auth.verify-email')
            ->layout('components.layouts.auth', ['title' => 'Verify Email — Fenroy']);
    }
}
