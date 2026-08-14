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
<body class="min-h-screen bg-brand-bg font-sans text-brand-text antialiased flex flex-col items-center justify-center px-4 py-12">

    <div class="w-full max-w-[440px]">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/fenroy-logo.png') }}" alt="Fenroy" class="h-8 w-auto mx-auto">
            </a>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl border border-brand-border-light p-8" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
            {{ $slot }}
        </div>

        {{-- Back link --}}
        <p class="text-center text-[13px] text-brand-muted mt-6">
            <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors">← Back to store</a>
        </p>

    </div>

    @livewireScripts
</body>
</html>
