<x-layouts.storefront title="Contact Us — Fenroy">
<style>
.contact-layout {
    display: flex;
    flex-direction: column;
    gap: 40px;
}
@media (min-width: 768px) {
    .contact-layout {
        display: grid !important;
        grid-template-columns: 380px 1fr;
        gap: 56px;
    }
}
</style>
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-8 md:py-10">

    <div class="contact-layout">

        {{-- Left column --}}
        <div>
            <nav class="text-[12px] text-brand-muted mb-5 flex items-center gap-1.5">
                <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors">Home</a>
                <span>/</span>
                <span class="text-brand-text">Contact</span>
            </nav>

            <h1 class="text-[32px] md:text-[38px] font-extrabold tracking-[-0.02em] text-brand-text leading-tight mb-3">
                Talk to us
            </h1>
            <p class="text-[14px] text-brand-secondary-text leading-[1.7] mb-7">
                Questions about an order, a product or delivery? We're quickest on WhatsApp.
            </p>

            {{-- Channel cards --}}
            <div class="flex flex-col gap-3 mb-6">

                {{-- WhatsApp --}}
                <a href="https://wa.me/233551234567" target="_blank" rel="noopener"
                   class="flex items-center gap-4 p-4 border border-[#E5E5E5] rounded-xl hover:border-brand-red transition-colors group">
                    <div class="w-11 h-11 rounded-xl bg-[#E8F5E9] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-[#2E7D32]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-brand-text">WhatsApp</p>
                        <p class="text-[13px] text-brand-secondary-text">055 123 4567 · replies in minutes</p>
                    </div>
                    <svg class="w-4 h-4 text-brand-muted group-hover:text-brand-red transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>

                {{-- Call us --}}
                <a href="tel:+233302555019"
                   class="flex items-center gap-4 p-4 border border-[#E5E5E5] rounded-xl hover:border-brand-red transition-colors group">
                    <div class="w-11 h-11 rounded-xl bg-brand-light-red flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-brand-dark-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.83a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-brand-text">Call us</p>
                        <p class="text-[13px] text-brand-secondary-text">0302 555 019 · 8am–9pm daily</p>
                    </div>
                    <svg class="w-4 h-4 text-brand-muted group-hover:text-brand-red transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>

                {{-- Email --}}
                <a href="mailto:hello@fenroy.com"
                   class="flex items-center gap-4 p-4 border border-[#E5E5E5] rounded-xl hover:border-brand-red transition-colors group">
                    <div class="w-11 h-11 rounded-xl bg-[#E3F2FD] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-[#1565C0]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-brand-text">Email</p>
                        <p class="text-[13px] text-brand-secondary-text">hello@fenroy.com · within 24 hours</p>
                    </div>
                    <svg class="w-4 h-4 text-brand-muted group-hover:text-brand-red transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Store & pickup --}}
            <div class="border border-[#E5E5E5] rounded-xl p-5 mb-6">
                <h3 class="text-[14px] font-bold text-brand-text mb-2">Store & pickup point</h3>
                <p class="text-[13px] text-brand-secondary-text mb-1">12 Oxford Street, Osu, Accra — near Danquah Circle</p>
                <p class="text-[13px] text-brand-secondary-text mb-4">Mon–Sat 7am–9pm · Sun 9am–7pm</p>
                {{-- Striped map slot --}}
                <div class="w-full h-[120px] rounded-lg overflow-hidden"
                     style="background: repeating-linear-gradient(45deg, #f0f0f0 0, #f0f0f0 1px, #fafafa 0, #fafafa 50%); background-size: 16px 16px;">
                    <div class="w-full h-full flex items-center justify-center">
                        <span class="text-[11px] text-brand-muted bg-white/70 px-2 py-0.5 rounded">map embed</span>
                    </div>
                </div>
            </div>

            <p class="text-[12px] text-brand-muted italic">
                All contact details shown are samples — swap in real ones before launch.
            </p>
        </div>

        {{-- Right column: Livewire form --}}
        <div>
            <livewire:contact-form />
        </div>
    </div>

</div>
</x-layouts.storefront>
