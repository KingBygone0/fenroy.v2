<?php

namespace App\Livewire\Pos;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileSettings extends Component
{
    use WithFileUploads;

    private const SAFE_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

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

        // Derive extension from server-detected MIME type — never trust client filename.
        $ext = self::SAFE_MIME_EXTENSIONS[$this->avatarUpload->getMimeType()] ?? null;
        if (! $ext) {
            $this->addError('avatarUpload', 'Unsupported image format.');
            return;
        }

        $filename = Str::random(40) . '.' . $ext;
        $path     = $this->avatarUpload->storeAs('avatars', $filename, 'public');
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
            'newPassword'     => ['required', Password::min(12)->letters()->mixedCase()->numbers()],
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
