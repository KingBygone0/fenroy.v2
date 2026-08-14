<div>
    {{-- Title --}}
    <div style="text-align:center; margin-bottom:32px;">
        <h1 style="margin:0; font-size:24px; font-weight:800; color:#171717; letter-spacing:-0.02em;">Create your account</h1>
        <p style="margin:4px 0 0; font-size:13px; color:#666666;">Shop groceries on Fenroy</p>
    </div>

    <form wire:submit="register" novalidate>

        {{-- Full name --}}
        <div style="margin-bottom:16px;">
            <label for="name" style="display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:6px;">Full name</label>
            <input wire:model="name"
                   id="name" type="text" autocomplete="name" placeholder="e.g. Ama Mensah"
                   style="width:100%; height:44px; padding:0 16px; border-radius:10px; background:#FFFFFF; border:1px solid {{ $errors->has('name') ? '#D32F2F' : '#D4D4D4' }}; font-size:14px; font-family:inherit; color:#171717; box-sizing:border-box; outline:none; transition:border-color 0.15s, box-shadow 0.15s;"
                   onfocus="this.style.borderColor='#E53935'; this.style.boxShadow='0 0 0 3px #FFEBEE';"
                   onblur="this.style.borderColor='{{ $errors->has('name') ? '#D32F2F' : '#D4D4D4' }}'; this.style.boxShadow='none';">
            @error('name')
            <p style="margin:4px 0 0; font-size:12px; color:#D32F2F;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div style="margin-bottom:16px;">
            <label for="email" style="display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:6px;">Email address</label>
            <input wire:model="email"
                   id="email" type="email" autocomplete="email" placeholder="you@example.com"
                   style="width:100%; height:44px; padding:0 16px; border-radius:10px; background:#FFFFFF; border:1px solid {{ $errors->has('email') ? '#D32F2F' : '#D4D4D4' }}; font-size:14px; font-family:inherit; color:#171717; box-sizing:border-box; outline:none; transition:border-color 0.15s, box-shadow 0.15s;"
                   onfocus="this.style.borderColor='#E53935'; this.style.boxShadow='0 0 0 3px #FFEBEE';"
                   onblur="this.style.borderColor='{{ $errors->has('email') ? '#D32F2F' : '#D4D4D4' }}'; this.style.boxShadow='none';">
            @error('email')
            <p style="margin:4px 0 0; font-size:12px; color:#D32F2F;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div style="margin-bottom:16px;" x-data="{ show: false }">
            <label for="password" style="display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:6px;">Password</label>
            <div style="position:relative;">
                <input wire:model="password"
                       id="password" :type="show ? 'text' : 'password'" autocomplete="new-password" placeholder="Min. 8 characters"
                       style="width:100%; height:44px; padding:0 44px 0 16px; border-radius:10px; background:#FFFFFF; border:1px solid {{ $errors->has('password') ? '#D32F2F' : '#D4D4D4' }}; font-size:14px; font-family:inherit; color:#171717; box-sizing:border-box; outline:none; transition:border-color 0.15s, box-shadow 0.15s;"
                       onfocus="this.style.borderColor='#E53935'; this.style.boxShadow='0 0 0 3px #FFEBEE';"
                       onblur="this.style.borderColor='{{ $errors->has('password') ? '#D32F2F' : '#D4D4D4' }}'; this.style.boxShadow='none';">
                <button type="button" @click="show = !show" aria-label="Toggle password visibility"
                        style="position:absolute; right:12px; top:50%; transform:translateY(-50%); width:28px; height:28px; display:flex; align-items:center; justify-content:center; background:none; border:none; padding:0; cursor:pointer; color:#999999; transition:color 0.15s;"
                        onmouseover="this.style.color='#171717'" onmouseout="this.style.color='#999999'">
                    <svg x-show="!show" style="width:16px; height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="show"  style="width:16px; height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            @error('password')
            <p style="margin:4px 0 0; font-size:12px; color:#D32F2F;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm password --}}
        <div style="margin-bottom:24px;" x-data="{ show: false }">
            <label for="password_confirmation" style="display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:6px;">Confirm password</label>
            <div style="position:relative;">
                <input wire:model="password_confirmation"
                       id="password_confirmation" :type="show ? 'text' : 'password'" autocomplete="new-password" placeholder="Repeat your password"
                       style="width:100%; height:44px; padding:0 44px 0 16px; border-radius:10px; background:#FFFFFF; border:1px solid #D4D4D4; font-size:14px; font-family:inherit; color:#171717; box-sizing:border-box; outline:none; transition:border-color 0.15s, box-shadow 0.15s;"
                       onfocus="this.style.borderColor='#E53935'; this.style.boxShadow='0 0 0 3px #FFEBEE';"
                       onblur="this.style.borderColor='#D4D4D4'; this.style.boxShadow='none';">
                <button type="button" @click="show = !show" aria-label="Toggle confirm password visibility"
                        style="position:absolute; right:12px; top:50%; transform:translateY(-50%); width:28px; height:28px; display:flex; align-items:center; justify-content:center; background:none; border:none; padding:0; cursor:pointer; color:#999999; transition:color 0.15s;"
                        onmouseover="this.style.color='#171717'" onmouseout="this.style.color='#999999'">
                    <svg x-show="!show" style="width:16px; height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="show"  style="width:16px; height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                wire:loading.attr="disabled"
                style="width:100%; height:48px; border-radius:9999px; background:#E53935; border:none; color:#FFFFFF; font-family:inherit; font-weight:600; font-size:15px; cursor:pointer; transition:background-color 0.15s; display:flex; align-items:center; justify-content:center; gap:8px;"
                onmouseover="if(!this.disabled) this.style.background='#B71C1C'" onmouseout="this.style.background='#E53935'">
            <svg wire:loading style="width:16px; height:16px; animation:spin 0.8s linear infinite;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            <span wire:loading.remove>Create account</span>
            <span wire:loading>Creating…</span>
        </button>

    </form>

    {{-- Terms micro-copy --}}
    <p style="margin:16px 0 0; text-align:center; font-size:12px; color:#999999; line-height:1.6;">
        By creating an account you agree to our
        <a href="{{ route('privacy') }}" style="color:#666666; text-decoration:underline;">Terms & Conditions</a>
        and
        <a href="{{ route('privacy') }}" style="color:#666666; text-decoration:underline;">Privacy Policy</a>.
    </p>

    {{-- Divider --}}
    <div style="margin:24px 0; display:flex; align-items:center; gap:12px;">
        <div style="flex:1; height:1px; background:#F0F0F0;"></div>
        <span style="font-size:12px; color:#999999;">or</span>
        <div style="flex:1; height:1px; background:#F0F0F0;"></div>
    </div>

    <p style="margin:0; text-align:center; font-size:13px; color:#666666;">
        Already have an account?
        <a href="{{ route('login') }}" style="font-weight:600; color:#E53935; text-decoration:none;"
           onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
            Sign in
        </a>
    </p>
</div>

<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
