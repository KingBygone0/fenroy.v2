<div>
    <div style="text-align:center; margin-bottom:32px;">
        <h1 style="margin:0; font-size:24px; font-weight:800; color:#171717; letter-spacing:-0.02em;">Set new password</h1>
        <p style="margin:4px 0 0; font-size:13px; color:#666666;">Choose a strong password for your account</p>
    </div>

    @if($error)
    <div style="margin-bottom:20px; padding:12px 16px; background:#FFF0F0; border:1px solid #FFCDD2; border-radius:12px; font-size:13px; color:#D32F2F;">
        {{ $error }}
        @if(str_contains($error, 'invalid or has expired'))
        <br><a href="{{ route('password.request') }}" style="color:#D32F2F; font-weight:600;">Request a new link →</a>
        @endif
    </div>
    @endif

    <form wire:submit="savePassword" novalidate>

        <div style="margin-bottom:16px;" x-data="{ show: false }">
            <label for="pw" style="display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:6px;">New password</label>
            <div style="position:relative;">
                <input wire:model="password"
                       id="pw" :type="show ? 'text' : 'password'" autocomplete="new-password" placeholder="Min. 12 chars, upper, lower &amp; number"
                       style="width:100%; height:44px; padding:0 44px 0 16px; border-radius:10px; background:#FFFFFF; border:1px solid {{ $errors->has('password') ? '#D32F2F' : '#D4D4D4' }}; font-size:14px; font-family:inherit; color:#171717; box-sizing:border-box; outline:none;"
                       onfocus="this.style.borderColor='#E53935'; this.style.boxShadow='0 0 0 3px #FFEBEE';"
                       onblur="this.style.borderColor='{{ $errors->has('password') ? '#D32F2F' : '#D4D4D4' }}'; this.style.boxShadow='none';">
                <button type="button" @click="show=!show" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#999; padding:0;" aria-label="Toggle visibility">
                    <svg x-show="!show" style="width:16px; height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="show"  style="width:16px; height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            @error('password')
            <p style="margin:4px 0 0; font-size:12px; color:#D32F2F;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:24px;">
            <label for="pw2" style="display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:6px;">Confirm new password</label>
            <input wire:model="password_confirmation"
                   id="pw2" type="password" autocomplete="new-password" placeholder="Repeat password"
                   style="width:100%; height:44px; padding:0 16px; border-radius:10px; background:#FFFFFF; border:1px solid #D4D4D4; font-size:14px; font-family:inherit; color:#171717; box-sizing:border-box; outline:none;"
                   onfocus="this.style.borderColor='#E53935'; this.style.boxShadow='0 0 0 3px #FFEBEE';"
                   onblur="this.style.borderColor='#D4D4D4'; this.style.boxShadow='none';">
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                style="width:100%; height:48px; border-radius:9999px; background:#E53935; border:none; color:#FFFFFF; font-family:inherit; font-weight:600; font-size:15px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;"
                onmouseover="if(!this.disabled) this.style.background='#B71C1C'" onmouseout="this.style.background='#E53935'">
            <svg wire:loading style="width:16px; height:16px; animation:spin 0.8s linear infinite;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            <span wire:loading.remove>Update password</span>
            <span wire:loading>Saving…</span>
        </button>
    </form>
</div>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
