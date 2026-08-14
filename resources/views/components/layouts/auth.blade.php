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
            <a href="{{ route('home') }}" style="display:inline-flex; align-items:center;">
                <img src="{{ asset('images/fenroy-logo.png') }}" alt="Fenroy" style="height:32px; width:auto; display:block;">
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
