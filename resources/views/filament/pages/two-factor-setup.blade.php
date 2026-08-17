<x-filament-panels::page>
    <div style="max-width:540px;">

        @if($this->isTwoFactorEnabled())
            {{-- ── Enabled state ── --}}
            <div style="padding:20px 24px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <svg style="width:24px; height:24px; color:#16a34a; flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div>
                        <p style="margin:0; font-weight:700; font-size:15px; color:#166534;">Two-factor authentication is active</p>
                        <p style="margin:2px 0 0; font-size:13px; color:#166534;">Your account requires a TOTP code on every admin login.</p>
                    </div>
                </div>
            </div>

            <button wire:click="disable" wire:confirm="Disable two-factor authentication? Your account will only require a password."
                    style="padding:10px 24px; border-radius:9999px; background:#fee2e2; border:1px solid #fecaca; color:#991b1b; font-family:inherit; font-weight:600; font-size:14px; cursor:pointer;">
                Disable 2FA
            </button>

        @elseif($this->getQrCodeUrl())
            {{-- ── Setup in progress ── --}}
            <p style="margin:0 0 16px; font-size:14px; color:var(--c-text-secondary, #6b7280); line-height:1.6;">
                Scan the QR code below with your authenticator app (Google Authenticator, Authy, 1Password, etc.), then enter the 6-digit code to confirm setup.
            </p>

            {{-- QR Code via Google Charts API --}}
            <div style="text-align:center; margin-bottom:20px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($this->getQrCodeUrl()) }}"
                     alt="TOTP QR Code" width="200" height="200"
                     style="border:8px solid white; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
            </div>

            <p style="margin:0 0 8px; font-size:12px; color:var(--c-text-secondary, #6b7280);">Or enter this secret manually:</p>
            <code style="display:block; padding:10px 16px; background:var(--c-bg-secondary, #f9fafb); border:1px solid var(--c-divider, #e5e7eb); border-radius:8px; font-size:15px; letter-spacing:0.15em; margin-bottom:24px; word-break:break-all;">
                {{ $this->getPendingPlainSecret() }}
            </code>

            <x-filament-panels::form wire:submit="enable">
                {{ $this->form }}
                <div style="margin-top:16px;">
                    <x-filament::button type="submit">Verify and enable 2FA</x-filament::button>
                </div>
            </x-filament-panels::form>

        @else
            {{-- ── Not enabled ── --}}
            <div style="padding:20px 24px; background:#fefce8; border:1px solid #fde68a; border-radius:12px; margin-bottom:24px;">
                <p style="margin:0; font-size:14px; color:#92400e; line-height:1.6;">
                    <strong>Two-factor authentication is not enabled.</strong><br>
                    Adding a second factor significantly reduces account takeover risk if your password is ever compromised.
                </p>
            </div>

            <button wire:click="generateSecret"
                    style="padding:10px 24px; border-radius:9999px; background:#dc2626; border:none; color:white; font-family:inherit; font-weight:600; font-size:14px; cursor:pointer;">
                Set up two-factor authentication
            </button>
        @endif

    </div>
</x-filament-panels::page>
