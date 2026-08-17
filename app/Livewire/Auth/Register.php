<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Register extends Component
{
    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('required|email|unique:users,email', message: ['email.unique' => 'Unable to create account with these details.'])]
    public string $email = '';

    #[Rule('required|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $this->validate();

        $user = User::create([
            'name'     => strip_tags($this->name),
            'email'    => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Log::channel('single')->info('New account registered', ['user_id' => $user->id, 'ip' => request()->ip()]);

        Auth::login($user);
        session()->regenerate();
        // New accounts start with an empty cart — no previous guest session
        session()->forget(['cart_items', 'cart_count', 'cart_total', 'cart_discount', 'cart_coupon', 'pending_order']);

        $this->redirect(route('account.profile'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.auth', ['title' => 'Create Account — Fenroy']);
    }
}
