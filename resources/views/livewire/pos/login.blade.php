<div style="width:100%;max-width:380px;background:#fff;border-radius:16px;padding:40px 36px;box-shadow:0 4px 24px rgba(0,0,0,0.1);">

    {{-- Logo --}}
    <div style="text-align:center;margin-bottom:28px;">
        <img src="https://fenroy.shop/images/fenroy-logo.png" alt="Fenroy" style="height:48px;width:auto;margin-bottom:12px;">
        <h1 style="font-size:20px;font-weight:800;color:#111;margin:0 0 4px;">Staff Terminal</h1>
        <p style="font-size:13px;color:#9ca3af;margin:0;">Sign in to access the POS</p>
    </div>

    @if($error)
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#dc2626;">
        {{ $error }}
    </div>
    @endif

    <div style="margin-bottom:14px;">
        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Email address</label>
        <input
            wire:model="email"
            type="email"
            autocomplete="email"
            placeholder="you@fenroy.shop"
            style="width:100%;height:42px;padding:0 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;"
            onfocus="this.style.borderColor='#C8102E';this.style.boxShadow='0 0 0 3px rgba(200,16,46,0.1)'"
            onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'"
            wire:keydown.enter="login"
        >
        @error('email') <p style="font-size:11px;color:#dc2626;margin:4px 0 0;">{{ $message }}</p> @enderror
    </div>

    <div style="margin-bottom:24px;">
        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Password</label>
        <input
            wire:model="password"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            style="width:100%;height:42px;padding:0 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;"
            onfocus="this.style.borderColor='#C8102E';this.style.boxShadow='0 0 0 3px rgba(200,16,46,0.1)'"
            onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'"
            wire:keydown.enter="login"
        >
        @error('password') <p style="font-size:11px;color:#dc2626;margin:4px 0 0;">{{ $message }}</p> @enderror
    </div>

    <button
        wire:click="login"
        wire:loading.attr="disabled"
        type="button"
        style="width:100%;height:44px;background:#C8102E;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;"
        onmouseenter="this.style.background='#a80000'" onmouseleave="this.style.background='#C8102E'"
    >
        <svg wire:loading style="width:16px;height:16px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
            <circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <span wire:loading.remove>Sign in</span>
        <span wire:loading>Signing in…</span>
    </button>

    <p style="text-align:center;font-size:11px;color:#d1d5db;margin:20px 0 0;">
        Fenroy POS &mdash; Authorised staff only
    </p>
</div>

<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
