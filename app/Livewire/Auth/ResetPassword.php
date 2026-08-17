<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Component;

class ResetPassword extends Component
{
    public string $token = '';
    public string $email = '';

    public string $password              = '';
    public string $password_confirmation = '';
    public string $error                 = '';

    // Generic message for both invalid token and invalid email — prevents account enumeration
    private const GENERIC_ERROR = 'This reset link is invalid or has expired. Please request a new one.';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function savePassword(): void
    {
        // Rate limit reset attempts — prevents token brute-force even though tokens are 60-char random
        $key = 'pw.reset:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->error = "Too many attempts. Please wait {$seconds} seconds.";
            return;
        }

        $this->validate(
            [
                'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()],
            ],
            [
                'password.min'     => 'Password must be at least 12 characters.',
                'password.letters' => 'Password must contain at least one letter.',
                'password.mixed'   => 'Password must contain both uppercase and lowercase letters.',
                'password.numbers' => 'Password must contain at least one number.',
            ]
        );

        RateLimiter::hit($key, 900);

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

                // Kill ALL existing sessions for this user — Auth::logoutOtherDevices() only works
                // when the user is currently authenticated, which they are not on the reset page.
                // Deleting directly from the sessions table is the only reliable approach.
                DB::table('sessions')->where('user_id', $user->id)->delete();

                Log::channel('single')->info('Password reset completed', [
                    'user_id' => $user->id,
                    'ip'      => request()->ip(),
                ]);
            }
        );

        if ($result === Password::PASSWORD_RESET) {
            RateLimiter::clear($key);
            $this->redirect(route('login') . '?reset=1', navigate: true);
            return;
        }

        // Use the same generic message for INVALID_TOKEN and INVALID_USER to prevent enumeration
        $this->error = self::GENERIC_ERROR;
    }

    public function render()
    {
        return view('livewire.auth.reset-password')
            ->layout('components.layouts.auth', ['title' => 'Set New Password — Fenroy']);
    }
}
