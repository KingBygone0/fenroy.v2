<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AccountProfile extends Component
{
    use WithFileUploads;

    #[Rule('required|min:2|max:100', message: 'Enter your full name.')]
    public string $name = '';

    // Uniqueness is enforced in save() with ->ignore(auth()->id()) — cannot be done in #[Rule]
    #[Rule('required|email|max:254', message: 'Enter a valid email address.')]
    public string $email = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    // mimes: validates via server-side finfo (actual bytes), not client-provided MIME.
    // dimensions: prevents memory exhaustion from oversized image bombs.
    // max:2048 = 2 MB cap.
    #[Rule('nullable|mimes:jpeg,jpg,png,webp,gif|max:2048|dimensions:max_width=4000,max_height=4000')]
    public $photo = null;

    private const SAFE_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

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
        $userId = Auth::id();

        $this->validate([
            'name'  => 'required|min:2|max:100',
            'email' => ['required', 'email', 'max:254', ValidationRule::unique('users', 'email')->ignore($userId)],
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|mimes:jpeg,jpg,png,webp,gif|max:2048|dimensions:max_width=4000,max_height=4000',
        ], [
            'email.unique' => 'That email address is already in use by another account.',
        ]);

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
            // Derive the extension from the server-detected MIME type (via finfo),
            // never from the client-supplied filename. This prevents polyglot
            // attacks where a PHP file passes image MIME detection but gets stored
            // with a .php extension that Apache would execute.
            $ext = self::SAFE_MIME_EXTENSIONS[$this->photo->getMimeType()] ?? null;
            if (! $ext) {
                $this->addError('photo', 'Unsupported image format.');
                return;
            }

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $filename        = Str::random(40) . '.' . $ext;
            $path            = $this->photo->storeAs('avatars', $filename, 'public');
            $data['avatar']  = $path;
            $this->avatarUrl = Storage::url($path);
            $this->photo     = null;
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
