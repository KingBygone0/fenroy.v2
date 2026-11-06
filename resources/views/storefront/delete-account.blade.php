<x-layouts.storefront title="Delete your account — Fenroy">
<div class="max-w-[720px] mx-auto px-4 md:px-6 py-8 md:py-12 pb-24 md:pb-12">

    <nav class="text-[12px] text-brand-muted mb-5 flex items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors">Home</a>
        <span>/</span>
        <span class="text-brand-text">Delete account</span>
    </nav>

    <h1 class="text-2xl md:text-3xl font-extrabold text-brand-text mb-3">Delete your Fenroy account</h1>
    <p class="text-sm text-brand-secondary-text mb-8">
        You can delete your account at any time, from the Fenroy app or this website.
    </p>

    <div class="bg-white rounded-2xl border border-brand-border-light p-6 mb-5">
        <h2 class="text-[17px] font-extrabold text-brand-text mb-3">How to delete it</h2>
        <ol class="list-decimal pl-5 space-y-2 text-sm text-brand-text">
            <li>Sign in to your account.</li>
            <li>Go to <strong>Account → Profile</strong>.</li>
            <li>Scroll to <strong>Delete account</strong> and tap <strong>Delete my account</strong>.</li>
            <li>Confirm with your password (or type DELETE if you signed up with Google).</li>
        </ol>

        <div class="mt-5">
            @auth
                <a href="{{ route('account.profile') }}#delete-account"
                   class="inline-flex items-center h-11 px-6 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors">
                    Go to my profile
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center h-11 px-6 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors">
                    Sign in to delete your account
                </a>
            @endauth
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-brand-border-light p-6 mb-5">
        <h2 class="text-[17px] font-extrabold text-brand-text mb-3">What gets deleted</h2>
        <ul class="list-disc pl-5 space-y-2 text-sm text-brand-text">
            <li>Your name, email address, phone number and profile photo</li>
            <li>Saved delivery addresses, wishlist, cart and reviews</li>
            <li>Sign-in history</li>
        </ul>
        <h2 class="text-[17px] font-extrabold text-brand-text mt-6 mb-3">What we keep</h2>
        <p class="text-sm text-brand-text">
            Records of past orders (items, amounts and dates) are kept for accounting and tax purposes,
            with your name, email, phone number and address removed. If you have an order that is still
            being delivered, you can delete your account once it has arrived.
        </p>
    </div>

    <p class="text-sm text-brand-secondary-text">
        Can't sign in? <a href="{{ route('contact') }}" class="text-brand-red hover:underline">Contact us</a>
        from the email address on your account and we'll delete it for you within 7 days.
    </p>
</div>
</x-layouts.storefront>
