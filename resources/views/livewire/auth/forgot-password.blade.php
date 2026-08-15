<div>
    <div style="text-align:center; margin-bottom:32px;">
        <h1 style="margin:0; font-size:24px; font-weight:800; color:#171717; letter-spacing:-0.02em;">Reset your password</h1>
        <p style="margin:4px 0 0; font-size:13px; color:#666666;">Enter your email and we'll send you a reset link</p>
    </div>

    @if($sent)
        <div style="padding:20px 24px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:12px; font-size:14px; color:#166534; line-height:1.5; margin-bottom:24px;">
            <svg style="display:inline; width:16px; height:16px; vertical-align:middle; margin-right:6px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
            {{ $status }}
        </div>
    @else

    @if($error)
    <div style="margin-bottom:20px; padding:12px 16px; background:#FFF0F0; border:1px solid #FFCDD2; border-radius:12px; font-size:13px; color:#D32F2F;">
        {{ $error }}
    </div>
    @endif

    <form wire:submit="send" novalidate>
        <div style="margin-bottom:20px;">
            <label for="email" style="display:block; font-size:13px; font-weight:600; color:#171717; margin-bottom:6px;">Email address</label>
            <input wire:model="email"
                   id="email" type="email" autocomplete="email" placeholder="you@example.com"
                   style="width:100%; height:44px; padding:0 16px; border-radius:10px; background:#FFFFFF; border:1px solid {{ $errors->has('email') ? '#D32F2F' : '#D4D4D4' }}; font-size:14px; font-family:inherit; color:#171717; box-sizing:border-box; outline:none;"
                   onfocus="this.style.borderColor='#E53935'; this.style.boxShadow='0 0 0 3px #FFEBEE';"
                   onblur="this.style.borderColor='{{ $errors->has('email') ? '#D32F2F' : '#D4D4D4' }}'; this.style.boxShadow='none';">
            @error('email')
            <p style="margin:4px 0 0; font-size:12px; color:#D32F2F;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                style="width:100%; height:48px; border-radius:9999px; background:#E53935; border:none; color:#FFFFFF; font-family:inherit; font-weight:600; font-size:15px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;"
                onmouseover="if(!this.disabled) this.style.background='#B71C1C'" onmouseout="this.style.background='#E53935'">
            <svg wire:loading style="width:16px; height:16px; animation:spin 0.8s linear infinite;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            <span wire:loading.remove>Send reset link</span>
            <span wire:loading>Sending…</span>
        </button>
    </form>
    @endif

    <p style="margin:24px 0 0; text-align:center; font-size:13px; color:#666666;">
        Remember it?
        <a href="{{ route('login') }}" style="font-weight:600; color:#E53935; text-decoration:none;"
           onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
            Sign in
        </a>
    </p>
</div>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
