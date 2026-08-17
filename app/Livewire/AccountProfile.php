<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AccountProfile extends Component
{
    use WithFileUploads;

    #[Rule('required|min:2|max:100', message: 'Enter your full name.')]
    public string $name = '';

    #[Rule('required|email|max:254', message: 'Enter a valid email address.')]
    public string $email = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('nullable|image|max:2048')]
    public $photo = null;

    // Required only when the user is changing their email — enforced in save()
    public string $current_password = '';

    public ?string $avatarUrl = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name      = $user->name  ?? '';
        $this->email     = $user->email ?? '';
        $this->phone     = $user->phone ?? '';
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

        // Changing email is a privileged operation — require current password to prevent
        // session hijacking leading to full account takeover via email change.
        if ($this->email !== $user->email) {
            if (empty($this->current_password) || ! Hash::check($this->current_password, $user->password)) {
                $this->addError('current_password', 'Enter your current password to change your email address.');
                return;
            }
        }

        $data = [
            'name'  => strip_tags($this->name),
            'email' => $this->email,
            'phone' => $this->phone ? strip_tags($this->phone) : null,
        ];

        if ($this->photo) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path           = $this->photo->store('avatars', 'public');
            $data['avatar'] = $path;
            $this->avatarUrl = Storage::url($path);
            $this->photo    = null;
        }

        $user->update($data);

        $this->current_password = '';

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
