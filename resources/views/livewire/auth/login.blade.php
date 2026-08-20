<div>
    {{-- Title --}}
    <div style="text-align:center; margin-bottom:32px;">
        <h1 style="margin:0; font-size:24px; font-weight:800; color:#171717; letter-spacing:-0.02em;">Welcome back</h1>
        <p style="margin:4px 0 0; font-size:13px; color:#666666;">Sign in to continue</p>
    </div>

    {{-- Password reset success --}}
    @if(request()->query('reset'))
    <div style="margin-bottom:20px; padding:12px 16px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:12px; font-size:13px; color:#166534;">
        ✓ Password updated successfully. Sign in with your new password.
    </div>
    @endif

    {{-- Error banner --}}
    @if($error)
    <div style="margin-bottom:20px; display:flex; align-items:flex-start; gap:12px; padding:12px 16px; background:#FFF0F0; border:1px solid #FFCDD2; border-radius:12px; font-size:13px; color:#D32F2F;">
        <svg style="width:16px; height:16px; flex-shrink:0; margin-top:2px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ $error }}</span>
    </div>
    @endif

    <form wire:submit="login" novalidate>

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
        <div style="margin-bottom:8px;" x-data="{ show: false }">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                <label for="password" style="font-size:13px; font-weight:600; color:#171717;">Password</label>
                <a href="{{ route('password.request') }}" style="font-size:12px; color:#B71C1C; text-decoration:none;"
                   onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                    Forgot password?
                </a>
            </div>
            <div style="position:relative;">
                <input wire:model="password"
                       id="password" :type="show ? 'text' : 'password'" autocomplete="current-password" placeholder="••••••••"
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

        {{-- Remember --}}
        <div style="display:flex; align-items:center; gap:8px; margin:16px 0 24px;">
            <input wire:model="remember" id="remember" type="checkbox"
                   style="width:16px; height:16px; border-radius:4px; accent-color:#E53935; cursor:pointer; margin:0;">
            <label for="remember" style="font-size:13px; color:#666666; cursor:pointer; user-select:none;">Keep me signed in</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                wire:loading.attr="disabled"
                style="width:100%; height:48px; border-radius:9999px; background:#E53935; border:none; color:#FFFFFF; font-family:inherit; font-weight:600; font-size:15px; cursor:pointer; transition:background-color 0.15s; display:flex; align-items:center; justify-content:center; gap:8px;"
                onmouseover="if(!this.disabled) this.style.background='#B71C1C'" onmouseout="this.style.background='#E53935'">
            <svg wire:loading style="width:16px; height:16px; animation:spin 0.8s linear infinite;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            <span wire:loading.remove>Sign in</span>
            <span wire:loading>Signing in…</span>
        </button>

    </form>

    {{-- Divider --}}
    <div style="margin:24px 0; display:flex; align-items:center; gap:12px;">
        <div style="flex:1; height:1px; background:#F0F0F0;"></div>
        <span style="font-size:12px; color:#999999;">or</span>
        <div style="flex:1; height:1px; background:#F0F0F0;"></div>
    </div>

    {{-- Google Sign-In --}}
    <a href="{{ route('auth.google') }}"
       style="width:100%; height:48px; border-radius:9999px; background:#FFFFFF; border:1px solid #D4D4D4; color:#171717; font-family:inherit; font-weight:600; font-size:15px; cursor:pointer; transition:background-color 0.15s, border-color 0.15s; display:flex; align-items:center; justify-content:center; gap:10px; text-decoration:none; box-sizing:border-box;"
       onmouseover="this.style.background='#F5F5F5'; this.style.borderColor='#B0B0B0';"
       onmouseout="this.style.background='#FFFFFF'; this.style.borderColor='#D4D4D4';">
        <svg style="width:20px; height:20px; flex-shrink:0;" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continue with Google
    </a>

    <div style="margin:24px 0 0;"></div>

    <p style="margin:0; text-align:center; font-size:13px; color:#666666;">
        Don't have an account?
        <a href="{{ route('register') }}" style="font-weight:600; color:#E53935; text-decoration:none;"
           onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
            Create one
        </a>
    </p>
</div>

<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
