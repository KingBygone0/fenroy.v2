<div>
    <div style="text-align:center; margin-bottom:32px;">
        <div style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:50%; background:#FEF2F2; margin-bottom:16px;">
            <svg style="width:28px; height:28px; color:#E53935;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/></svg>
        </div>
        <h1 style="margin:0; font-size:24px; font-weight:800; color:#171717; letter-spacing:-0.02em;">Verify your email</h1>
        <p style="margin:8px 0 0; font-size:13px; color:#666666; line-height:1.6;">
            We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.<br>
            Click the link in that email to activate your account.
        </p>
    </div>

    @if($sent)
        <div style="padding:16px 20px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:12px; font-size:14px; color:#166534; margin-bottom:24px;">
            <svg style="display:inline; width:16px; height:16px; vertical-align:middle; margin-right:6px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
            A new verification link has been sent to your email address.
        </div>
    @endif

    <button wire:click="resend" wire:loading.attr="disabled"
            style="width:100%; height:48px; border-radius:9999px; background:#E53935; border:none; color:#FFFFFF; font-family:inherit; font-weight:600; font-size:15px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;"
            onmouseover="if(!this.disabled) this.style.background='#B71C1C'" onmouseout="this.style.background='#E53935'">
        <svg wire:loading style="width:16px; height:16px; animation:spin 0.8s linear infinite;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        <span wire:loading.remove>Resend verification email</span>
        <span wire:loading>Sending…</span>
    </button>

    <form method="POST" action="{{ route('logout') }}" style="margin-top:16px; text-align:center;">
        @csrf
        <button type="submit" style="background:none; border:none; font-size:13px; color:#666666; cursor:pointer; font-family:inherit;">
            Sign out and use a different account
        </button>
    </form>
</div>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
