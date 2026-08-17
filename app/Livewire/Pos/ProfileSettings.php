<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;

class ProfileSettings extends Component
{
    use WithFileUploads;

    public string $name            = '';
    public string $phone           = '';
    public string $currentPassword = '';
    public string $newPassword     = '';
    public string $confirmPassword = '';
    public $avatarUpload           = null;
    public string $profileSuccess  = '';
    public string $passwordSuccess = '';
    public string $passwordError   = '';

    public function mount(): void
    {
        $this->name  = auth()->user()->name;
        $this->phone = auth()->user()->phone ?? '';
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        auth()->user()->update([
            'name'  => trim($this->name),
            'phone' => trim($this->phone) ?: null,
        ]);

        $this->profileSuccess = 'Profile updated successfully.';
    }

    public function updateAvatar(): void
    {
        $this->validate(['avatarUpload' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);

        $path = $this->avatarUpload->store('avatars', 'public');
        auth()->user()->update(['avatar' => $path]);

        $this->avatarUpload   = null;
        $this->profileSuccess = 'Profile photo updated.';
    }

    public function updatePassword(): void
    {
        $this->passwordError   = '';
        $this->passwordSuccess = '';

        $this->validate([
            'currentPassword' => 'required',
            'newPassword'     => 'required|min:8',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        if (! Hash::check($this->currentPassword, auth()->user()->password)) {
            $this->passwordError = 'Current password is incorrect.';
            return;
        }

        auth()->user()->update(['password' => Hash::make($this->newPassword)]);

        $this->currentPassword = '';
        $this->newPassword     = '';
        $this->confirmPassword = '';
        $this->passwordSuccess = 'Password changed successfully.';
    }

    public function render()
    {
        return view('livewire.pos.profile-settings')
            ->layout('layouts.pos');
    }
}
