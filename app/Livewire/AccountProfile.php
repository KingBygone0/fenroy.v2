<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AccountProfile extends Component
{
    use WithFileUploads;

    #[Rule('required|min:2', message: 'Enter your full name.')]
    public string $name = '';

    #[Rule('required|email', message: 'Enter a valid email address.')]
    public string $email = '';

    #[Rule('required', message: 'Enter your phone number.')]
    public string $phone = '';

    #[Rule('nullable|image|max:2048')]
    public $photo = null;

    public ?string $avatarUrl = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name  = $user->name  ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->avatarUrl = $user->avatar ? Storage::url($user->avatar) : null;
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo');
        $this->avatarUrl = $this->photo->temporaryUrl();
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $data = [
            'name'  => $this->name,
            'email' => $this->email,
        ];

        if ($this->photo) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $this->photo->store('avatars', 'public');
            $data['avatar'] = $path;
            $this->avatarUrl = Storage::url($path);
            $this->photo = null;
        }

        $user->update($data);

        $this->js("window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Profile saved successfully.' } }))");
    }

    #[Computed]
    public function initials(): string
    {
        $parts = explode(' ', trim($this->name));
        return strtoupper(
            ($parts[0][0] ?? '') . (isset($parts[1]) ? $parts[1][0] : '')
        );
    }

    public function render()
    {
        return view('livewire.account-profile', [
            'initials' => $this->initials,
        ]);
    }
}
