<div class="pos-grid-2">

    {{-- HEADER --}}
    <header class="pos-header">
        <div class="hdr-logo">
            <div style="width:38px;height:38px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <div>
                <p style="color:#fff;font-size:17px;font-weight:900;margin:0;letter-spacing:-.5px;line-height:1.15;">Fenroy</p>
                <p style="color:rgba(255,255,255,.55);font-size:10px;margin:0;font-weight:500;">POS System</p>
            </div>
        </div>
        <div style="flex:1;padding:0 28px;">
            <h1 style="font-size:17px;font-weight:800;color:var(--fen-text);margin:0;">Settings</h1>
            <p style="font-size:12px;color:var(--fen-muted);margin:0;">Profile &amp; security</p>
        </div>
        <div style="padding:0 20px;display:flex;align-items:center;gap:12px;">
            @livewire('pos.notification-bell')
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <div style="width:36px;height:36px;border-radius:50%;background:var(--fen-red);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                <div><p style="font-size:13px;font-weight:700;color:var(--fen-text);margin:0;white-space:nowrap;">{{ auth()->user()->name }}</p><p style="font-size:11px;color:var(--fen-muted);margin:0;">{{ auth()->user()->is_admin?'Admin':'Staff' }}</p></div>
            </div>
        </div>
    </header>

    {{-- SIDEBAR --}}
    @include('livewire.pos.partials.sidebar', ['activePage' => 'settings'])

    {{-- MAIN --}}
    <main class="pos-main">
        <div style="flex:1;overflow-y:auto;padding:24px;min-height:0;">
            <div style="max-width:640px;margin:0 auto;display:flex;flex-direction:column;gap:20px;">

                {{-- Component messages --}}
                @if($profileSuccess)
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;color:#166534;font-size:13px;font-weight:600;">✓ {{ $profileSuccess }}</div>
                @endif
                @if($passwordSuccess)
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;color:#166534;font-size:13px;font-weight:600;">✓ {{ $passwordSuccess }}</div>
                @endif
                @if($passwordError)
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;color:#991b1b;font-size:13px;font-weight:600;">✕ {{ $passwordError }}</div>
                @endif

                {{-- Avatar card --}}
                <div style="background:var(--fen-white);border:1px solid var(--fen-border);border-radius:12px;padding:24px;">
                    <h2 style="font-size:14px;font-weight:800;color:var(--fen-text);margin:0 0 16px;">Profile Photo</h2>
                    <div style="display:flex;align-items:center;gap:20px;">
                        <div style="width:72px;height:72px;border-radius:50%;overflow:hidden;background:var(--fen-red);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            @if(auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                            @else
                            <span style="font-size:28px;font-weight:800;color:#fff;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
                            @endif
                        </div>
                        <div style="flex:1;">
                            <input wire:model="avatarUpload" type="file" accept="image/*" id="avatar-upload" style="display:none;" wire:change="updateAvatar">
                            <label for="avatar-upload" style="display:inline-block;padding:8px 16px;background:var(--fen-bg);border:1.5px solid var(--fen-border);border-radius:8px;font-size:13px;font-weight:600;color:var(--fen-text);cursor:pointer;">
                                Upload Photo
                            </label>
                            <p style="font-size:12px;color:var(--fen-muted);margin:8px 0 0;">JPG, PNG or GIF · Max 2MB</p>
                        </div>
                    </div>
                </div>

                {{-- Profile info card --}}
                <div style="background:var(--fen-white);border:1px solid var(--fen-border);border-radius:12px;padding:24px;">
                    <h2 style="font-size:14px;font-weight:800;color:var(--fen-text);margin:0 0 16px;">Personal Information</h2>
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Full Name</label>
                            <input wire:model="name" type="text" class="pos-input" placeholder="Your full name">
                            @error('name')<p style="color:var(--fen-danger);font-size:12px;margin:4px 0 0;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Phone</label>
                            <input wire:model="phone" type="tel" class="pos-input" placeholder="0XX XXX XXXX">
                            @error('phone')<p style="color:var(--fen-danger);font-size:12px;margin:4px 0 0;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Email (read only)</label>
                            <input type="email" value="{{ auth()->user()->email }}" class="pos-input" disabled style="background:var(--fen-bg);cursor:not-allowed;color:var(--fen-muted);">
                        </div>
                        <button wire:click="updateProfile" type="button"
                            style="align-self:flex-start;padding:10px 24px;background:var(--fen-red);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">
                            Save Changes
                        </button>
                    </div>
                </div>

                {{-- Change password card --}}
                <div style="background:var(--fen-white);border:1px solid var(--fen-border);border-radius:12px;padding:24px;">
                    <h2 style="font-size:14px;font-weight:800;color:var(--fen-text);margin:0 0 16px;">Change Password</h2>
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Current Password</label>
                            <input wire:model="currentPassword" type="password" class="pos-input" placeholder="Enter current password">
                            @error('currentPassword')<p style="color:var(--fen-danger);font-size:12px;margin:4px 0 0;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">New Password</label>
                            <input wire:model="newPassword" type="password" class="pos-input" placeholder="Min 8 characters">
                            @error('newPassword')<p style="color:var(--fen-danger);font-size:12px;margin:4px 0 0;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Confirm New Password</label>
                            <input wire:model="confirmPassword" type="password" class="pos-input" placeholder="Repeat new password">
                        </div>
                        <button wire:click="updatePassword" type="button"
                            style="align-self:flex-start;padding:10px 24px;background:var(--fen-text);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">
                            Update Password
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
