<x-layouts.storefront title="About — Fenroy">
<style>
.about-howwework {
    display: flex;
    flex-direction: column;
    gap: 48px;
}
@media (min-width: 768px) {
    .about-howwework {
        display: grid !important;
        grid-template-columns: 1fr 380px;
        gap: 64px;
    }
}
</style>
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-8 md:py-10">

    {{-- Breadcrumb --}}
    <nav class="text-[12px] text-brand-muted mb-5 flex items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors">Home</a>
        <span>/</span>
        <span class="text-brand-text">About</span>
    </nav>

    {{-- H1 + Intro --}}
    <div class="mb-8" style="max-width:820px;">
        <h1 class="text-[40px] md:text-[48px] font-extrabold tracking-[-0.02em] text-brand-text leading-[1.1] mb-5">
            Your neighbourhood supermarket,<br>now online.
        </h1>
        <p class="text-[15px] text-brand-secondary-text leading-[1.7]">
            Fenroy started as a single supermarket serving families across Accra. Today we bring the same fresh produce, trusted brands and friendly service to your door — same-day, seven days a week. Every order is picked by our own staff, packed with care, and delivered by riders who know your neighbourhood.
        </p>
    </div>

    {{-- Stat strip --}}
    <div class="border-t border-b border-[#E5E5E5] mb-12">
        <div class="grid grid-cols-2 md:grid-cols-4">
            @foreach([
                ['figure' => '2,400+', 'label' => 'products in store'],
                ['figure' => '12',     'label' => 'delivery zones in Accra'],
                ['figure' => '3h',     'label' => 'average delivery time'],
                ['figure' => '4.8★',   'label' => 'from 1,900+ customers'],
            ] as $i => $stat)
            <div class="py-6 px-4 md:px-6 {{ $i < 3 ? 'md:border-r border-[#E5E5E5]' : '' }} {{ $i === 1 ? 'border-l border-[#E5E5E5] md:border-l-0' : '' }}">
                <p class="text-[28px] font-extrabold text-brand-text leading-none mb-1">{{ $stat['figure'] }}</p>
                <p class="text-[13px] text-brand-secondary-text">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Two-col: How we work + Visit us --}}
    <div class="about-howwework">

        {{-- How we work --}}
        <div>
            <h2 class="text-[20px] font-extrabold text-brand-text mb-7">How we work</h2>
            <div class="flex flex-col gap-8">
                @foreach([
                    [
                        'n'     => '1',
                        'title' => 'Fresh stock every morning',
                        'body'  => 'Produce arrives from Agbogbloshie and Makola markets before 7am. What you see online is what\'s on our shelves — stock levels update live.',
                    ],
                    [
                        'n'     => '2',
                        'title' => 'Picked by people who care',
                        'body'  => 'Our trained pickers choose your items the way you would — ripest fruit, furthest expiry dates. If something\'s out, we call you before substituting.',
                    ],
                    [
                        'n'     => '3',
                        'title' => 'Delivered on your schedule',
                        'body'  => 'Choose a delivery window at checkout. Track your order from picking to your doorstep, and pay with MoMo or card — no cash needed.',
                    ],
                ] as $step)
                <div class="flex gap-4">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-brand-light-red flex items-center justify-center mt-0.5">
                        <span class="text-[13px] font-bold text-brand-dark-red">{{ $step['n'] }}</span>
                    </div>
                    <div>
                        <p class="text-[15px] font-bold text-brand-text mb-1.5">{{ $step['title'] }}</p>
                        <p class="text-[14px] text-brand-secondary-text leading-[1.6]">{{ $step['body'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Photo + Visit us --}}
        <div class="flex flex-col gap-5">
            {{-- Diagonal-stripe photo slot --}}
            <div class="w-full h-[200px] rounded-xl overflow-hidden relative"
                 style="background: repeating-linear-gradient(45deg, #f0f0f0 0, #f0f0f0 1px, #fafafa 0, #fafafa 50%); background-size: 18px 18px;">
                <span class="absolute bottom-3 right-3 text-[11px] text-brand-muted bg-white/80 px-2 py-0.5 rounded">Storefront / team photo</span>
            </div>

            {{-- Visit us card --}}
            <div class="border border-[#E5E5E5] rounded-2xl p-5">
                <h3 class="text-[15px] font-bold text-brand-text mb-4">Visit us</h3>
                <div class="flex flex-col gap-3">
                    <div class="flex gap-3 text-[13px] text-brand-secondary-text">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>12 Oxford Street, Osu, Accra<br><span class="text-brand-muted">near Danquah Circle</span></span>
                    </div>
                    <div class="flex gap-3 text-[13px] text-brand-secondary-text">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Mon–Sat 7:00am–9:00pm<br>Sun 9:00am–7:00pm</span>
                    </div>
                    <div class="flex gap-3 text-[13px] text-brand-secondary-text">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.83a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></svg>
                        <span>0302 555 019 <span class="text-brand-muted italic">(sample)</span></span>
                    </div>
                    <div class="flex gap-3 text-[13px] text-brand-secondary-text">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                        <span>WhatsApp 055 123 4567</span>
                    </div>
                </div>
                <a href="https://maps.google.com/?q=12+Oxford+Street+Osu+Accra"
                   target="_blank" rel="noopener"
                   class="mt-5 inline-flex items-center h-9 px-5 rounded-full bg-brand-light-red text-brand-dark-red text-[13px] font-semibold hover:bg-brand-red hover:text-white transition-colors">
                    Get Directions
                </a>
            </div>
        </div>
    </div>

</div>
</x-layouts.storefront>
