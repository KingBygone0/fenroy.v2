<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Fenroy' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="margin:0; background:#F7F7F7; font-family:'Inter',ui-sans-serif,system-ui,sans-serif; -webkit-font-smoothing:antialiased; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:48px 16px; box-sizing:border-box;">

    <div style="width:100%; max-width:440px; margin:0 auto;">

        {{-- Logo --}}
        <div style="display:flex; justify-content:center; align-items:center; margin-bottom:28px;">
            <a href="{{ route('home') }}" style="display:inline-flex; align-items:center; gap:10px; text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="height:40px; width:40px; flex-shrink:0;">
                    <g transform="rotate(8, 256, 256)">
                        <rect x="28" y="28" width="456" height="456" rx="88" ry="88" fill="#A80000"/>
                    </g>
                    <path d="M108,230 L108,175 Q108,148 135,143 L182,148" fill="none" stroke="white" stroke-width="30" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="172" y1="148" x2="355" y2="182" stroke="white" stroke-width="30" stroke-linecap="round"/>
                    <path d="M172,148 L355,182 L322,300 L200,300 Z" fill="white"/>
                    <circle cx="218" cy="342" r="30" fill="white"/>
                    <circle cx="308" cy="342" r="30" fill="white"/>
                </svg>
                <span style="font-size:22px; font-weight:800; color:#A80000; letter-spacing:-0.03em;">Fenroy</span>
            </a>
        </div>

        {{-- Card --}}
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #F0F0F0; padding:32px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
            {{ $slot }}
        </div>

        {{-- Back link --}}
        <p style="text-align:center; font-size:13px; color:#999999; margin-top:24px;">
            <a href="{{ route('home') }}" style="color:#999999; text-decoration:none; transition:color 0.15s;"
               onmouseover="this.style.color='#171717'" onmouseout="this.style.color='#999999'">
                ← Back to store
            </a>
        </p>

    </div>

    @livewireScripts
</body>
</html>
