<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ResetPassword extends Component
{
    public string $token = '';
    public string $email = '';

    #[Rule('required|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';
    public string $error = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function savePassword(): void
    {
        $this->validate();
        $this->error = '';

        $result = Password::reset(
            [
                'email'                 => $this->email,
                'password'              => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token'                 => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($result === Password::PASSWORD_RESET) {
            $this->redirect(route('login') . '?reset=1', navigate: true);
            return;
        }

        $this->error = match ($result) {
            Password::INVALID_TOKEN    => 'This reset link is invalid or has expired. Please request a new one.',
            Password::INVALID_USER     => 'We could not find an account with that email.',
            default                    => 'Something went wrong. Please try again.',
        };
    }

    public function render()
    {
        return view('livewire.auth.reset-password')
            ->layout('components.layouts.auth', ['title' => 'Set New Password — Fenroy']);
    }
}
