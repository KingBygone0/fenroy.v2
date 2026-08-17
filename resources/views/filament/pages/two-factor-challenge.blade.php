<x-filament-panels::page>
    <x-filament-panels::form wire:submit="verify">
        <div style="text-align:center; margin-bottom:24px;">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:50%; background:#fee2e2; margin-bottom:12px;">
                <svg style="width:28px; height:28px; color:#dc2626;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/></svg>
            </div>
            <p style="margin:0; font-size:14px; color:#6b7280; line-height:1.5;">
                Enter the 6-digit code from your authenticator app.
            </p>
        </div>

        <x-filament::input.wrapper :valid="! $errors->has('code')">
            <x-filament::input
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                wire:model="code"
                placeholder="000000"
                maxlength="6"
                style="text-align:center; font-size:24px; letter-spacing:0.3em; font-weight:700;"
            />
        </x-filament::input.wrapper>
        @error('code')
            <p style="margin:4px 0 0; font-size:12px; color:#dc2626;">{{ $message }}</p>
        @enderror

        <x-filament::button type="submit" style="width:100%; margin-top:16px;">
            Verify
        </x-filament::button>
    </x-filament-panels::form>

    <div style="margin-top:16px; text-align:center;">
        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
            @csrf
            <button type="submit" style="background:none; border:none; font-size:13px; color:#6b7280; cursor:pointer; font-family:inherit;">
                Sign out
            </button>
        </form>
    </div>
</x-filament-panels::page>
