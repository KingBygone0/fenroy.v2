<x-layouts.storefront title="Privacy Policy — Fenroy">
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-8 md:py-10"
     x-data="{ policy: 'privacy' }">

    {{-- Mobile: horizontal chip switcher --}}
    <div class="md:hidden mb-6 overflow-x-auto -mx-4 px-4">
        <div class="flex gap-2 pb-1">
            @foreach(['privacy' => 'Privacy Policy', 'terms' => 'Terms & Conditions', 'refund' => 'Refund Policy', 'delivery' => 'Delivery Policy'] as $key => $label)
            <button @click="policy = '{{ $key }}'"
                    :class="policy === '{{ $key }}' ? 'bg-brand-light-red text-brand-dark-red font-bold border-brand-light-red' : 'bg-white text-brand-secondary-text border-[#E5E5E5] hover:text-brand-text'"
                    class="shrink-0 h-9 px-4 rounded-[10px] text-[13px] border transition-colors whitespace-nowrap">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    <div class="flex gap-14 items-start">

        {{-- Sidebar (desktop only) --}}
        <aside class="hidden md:block shrink-0" style="width:200px;">
            <div class="sticky top-[88px]">
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-2">Policies</p>
                <nav class="flex flex-col gap-0.5 mb-6">
                    @foreach(['privacy' => 'Privacy Policy', 'terms' => 'Terms & Conditions', 'refund' => 'Refund Policy', 'delivery' => 'Delivery Policy'] as $key => $label)
                    <button @click="policy = '{{ $key }}'"
                            :class="policy === '{{ $key }}' ? 'bg-brand-light-red text-brand-dark-red font-semibold' : 'text-brand-secondary-text hover:bg-[#F7F7F7]'"
                            class="w-full text-left px-3 py-2 rounded-[8px] text-[13px] transition-colors">
                        {{ $label }}
                    </button>
                    @endforeach
                </nav>

                <div class="border-t border-[#E5E5E5] pt-5">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-2">On this page</p>

                    {{-- Privacy TOC --}}
                    <div x-show="policy === 'privacy'" class="flex flex-col gap-0.5">
                        @foreach([
                            ['anchor' => 'what-we-collect', 'n' => '1', 'label' => 'What we collect'],
                            ['anchor' => 'how-we-use-it',   'n' => '2', 'label' => 'How we use it'],
                            ['anchor' => 'payments',         'n' => '3', 'label' => 'Payments'],
                            ['anchor' => 'sharing',          'n' => '4', 'label' => 'Sharing'],
                            ['anchor' => 'your-rights',      'n' => '5', 'label' => 'Your rights'],
                            ['anchor' => 'contact',          'n' => '6', 'label' => 'Contact'],
                        ] as $item)
                        <a href="#{{ $item['anchor'] }}" class="flex items-center gap-2 px-3 py-1.5 rounded text-[13px] text-brand-secondary-text hover:text-brand-dark-red transition-colors">
                            <span class="text-[11px] text-brand-muted w-3 shrink-0">{{ $item['n'] }}.</span>
                            {{ $item['label'] }}
                        </a>
                        @endforeach
                    </div>
                    {{-- Terms TOC --}}
                    <div x-show="policy === 'terms'" class="flex flex-col gap-0.5">
                        @foreach([
                            ['anchor' => 'use-of-service', 'n' => '1', 'label' => 'Use of service'],
                            ['anchor' => 'accounts',        'n' => '2', 'label' => 'Accounts'],
                            ['anchor' => 'orders-payment',  'n' => '3', 'label' => 'Orders & payment'],
                            ['anchor' => 'liability',       'n' => '4', 'label' => 'Liability'],
                            ['anchor' => 'changes',         'n' => '5', 'label' => 'Changes'],
                        ] as $item)
                        <a href="#{{ $item['anchor'] }}" class="flex items-center gap-2 px-3 py-1.5 rounded text-[13px] text-brand-secondary-text hover:text-brand-dark-red transition-colors">
                            <span class="text-[11px] text-brand-muted w-3 shrink-0">{{ $item['n'] }}.</span>
                            {{ $item['label'] }}
                        </a>
                        @endforeach
                    </div>
                    {{-- Refund TOC --}}
                    <div x-show="policy === 'refund'" class="flex flex-col gap-0.5">
                        @foreach([
                            ['anchor' => 'eligibility',     'n' => '1', 'label' => 'Eligibility'],
                            ['anchor' => 'how-to-request',  'n' => '2', 'label' => 'How to request'],
                            ['anchor' => 'timeline',        'n' => '3', 'label' => 'Refund timeline'],
                            ['anchor' => 'exceptions',      'n' => '4', 'label' => 'Exceptions'],
                        ] as $item)
                        <a href="#{{ $item['anchor'] }}" class="flex items-center gap-2 px-3 py-1.5 rounded text-[13px] text-brand-secondary-text hover:text-brand-dark-red transition-colors">
                            <span class="text-[11px] text-brand-muted w-3 shrink-0">{{ $item['n'] }}.</span>
                            {{ $item['label'] }}
                        </a>
                        @endforeach
                    </div>
                    {{-- Delivery TOC --}}
                    <div x-show="policy === 'delivery'" class="flex flex-col gap-0.5">
                        @foreach([
                            ['anchor' => 'zones',    'n' => '1', 'label' => 'Delivery zones'],
                            ['anchor' => 'windows',  'n' => '2', 'label' => 'Windows & cut-off'],
                            ['anchor' => 'fees',     'n' => '3', 'label' => 'Fees & free delivery'],
                            ['anchor' => 'failed',   'n' => '4', 'label' => 'Failed delivery'],
                        ] as $item)
                        <a href="#{{ $item['anchor'] }}" class="flex items-center gap-2 px-3 py-1.5 rounded text-[13px] text-brand-secondary-text hover:text-brand-dark-red transition-colors">
                            <span class="text-[11px] text-brand-muted w-3 shrink-0">{{ $item['n'] }}.</span>
                            {{ $item['label'] }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        {{-- Content --}}
        <div class="flex-1 min-w-0" style="max-width:680px;">

            {{-- Privacy Policy --}}
            <article x-show="policy === 'privacy'">
                <h1 class="text-[32px] font-extrabold tracking-[-0.02em] text-brand-text leading-tight mb-2">Privacy Policy</h1>
                <p class="text-[13px] text-brand-muted mb-6">Last updated 14 August 2026 · Applies to fenroy.com and the Fenroy storefront</p>
                <div class="bg-[#F9F9F9] rounded-xl px-5 py-4 mb-8 text-[14px] text-brand-secondary-text leading-[1.7]">
                    <strong class="text-brand-text">In short:</strong> we collect only what we need to deliver your groceries — your name, phone, addresses and email. We never sell your data, and payment details are handled by our payment providers, not stored by us.
                </div>
                <div class="prose-policy">
                    <h2 id="what-we-collect">1. What we collect</h2>
                    <p>When you create an account or place an order we collect your <strong>name, phone number and email</strong>; your <strong>delivery addresses and landmarks</strong>; your <strong>order history and shopping preferences</strong> (like substitution choices); and technical information such as device type and app version to keep the service working well.</p>
                    <h2 id="how-we-use-it">2. How we use it</h2>
                    <p>We use your information to process and deliver orders, contact you about substitutions or delivery, send order updates by SMS, improve the products we stock, and — only if you opt in — tell you about deals. You can turn marketing messages off any time in <strong>Account → Notifications</strong>.</p>
                    <h2 id="payments">3. Payments</h2>
                    <p>Mobile money and card payments are processed by licensed payment providers. Fenroy never sees or stores your full card number or MoMo PIN. We keep only a payment reference so we can match payments to orders and process refunds.</p>
                    <h2 id="sharing">4. Sharing</h2>
                    <p>We share your delivery address and phone number with the rider delivering your order, and order data with the payment provider handling your payment. We do not sell personal data to anyone.</p>
                    <h2 id="your-rights">5. Your rights</h2>
                    <p>You can view and edit your details in your account, ask us for a copy of your data, or ask us to delete your account entirely. Deleting your account removes your personal details; anonymised order records are kept for accounting as required by law.</p>
                    <h2 id="contact">6. Contact</h2>
                    <p>Questions about privacy? Email <a href="mailto:privacy@fenroy.com" class="text-brand-dark-red hover:underline">privacy@fenroy.com</a> or write to Fenroy, 12 Oxford Street, Osu, Accra. <span class="text-brand-muted italic">(Sample contact details)</span></p>
                </div>
            </article>

            {{-- Terms & Conditions --}}
            <article x-show="policy === 'terms'">
                <h1 class="text-[32px] font-extrabold tracking-[-0.02em] text-brand-text leading-tight mb-2">Terms & Conditions</h1>
                <p class="text-[13px] text-brand-muted mb-6">Last updated 14 August 2026 · Applies to fenroy.com and the Fenroy storefront</p>
                <div class="bg-[#F9F9F9] rounded-xl px-5 py-4 mb-8 text-[14px] text-brand-secondary-text leading-[1.7]">
                    <strong class="text-brand-text">In short:</strong> by using Fenroy you agree to these terms. We reserve the right to refuse or cancel orders. Prices and availability can change. We are not liable for delays caused by events outside our control.
                </div>
                <div class="prose-policy">
                    <h2 id="use-of-service">1. Use of service</h2>
                    <p>By accessing or using the Fenroy storefront you agree to be bound by these terms. You must be at least 18 years old, or have the consent of a parent or guardian, to place an order. We reserve the right to refuse service at our discretion.</p>
                    <h2 id="accounts">2. Accounts</h2>
                    <p>You are responsible for keeping your account credentials secure. Notify us immediately at <a href="mailto:hello@fenroy.com" class="text-brand-dark-red hover:underline">hello@fenroy.com</a> if you suspect unauthorised access. We may suspend accounts that violate these terms.</p>
                    <h2 id="orders-payment">3. Orders & payment</h2>
                    <p>Placing an order is an offer to purchase. We reserve the right to cancel orders where a product is out of stock, priced in error, or where fraud is suspected. Payment is collected at the time of ordering. Any refunds are processed within 1–5 business days to the original payment method.</p>
                    <h2 id="liability">4. Liability</h2>
                    <p>Fenroy's liability is limited to the value of the products in your order. We are not liable for indirect or consequential losses, or for delays caused by circumstances beyond our control (power outages, severe weather, civil unrest, etc.).</p>
                    <h2 id="changes">5. Changes to these terms</h2>
                    <p>We may update these terms from time to time. The updated version will be posted here with a revised date. Continued use of the service after changes are posted constitutes your acceptance.</p>
                </div>
            </article>

            {{-- Refund Policy --}}
            <article x-show="policy === 'refund'">
                <h1 class="text-[32px] font-extrabold tracking-[-0.02em] text-brand-text leading-tight mb-2">Refund Policy</h1>
                <p class="text-[13px] text-brand-muted mb-6">Last updated 14 August 2026 · Applies to fenroy.com and the Fenroy storefront</p>
                <div class="bg-[#F9F9F9] rounded-xl px-5 py-4 mb-8 text-[14px] text-brand-secondary-text leading-[1.7]">
                    <strong class="text-brand-text">In short:</strong> report problems within 24 hours of delivery. We'll refund or replace damaged, wrong or missing items promptly. Refunds go to your original payment method within 1–5 business days.
                </div>
                <div class="prose-policy">
                    <h2 id="eligibility">1. Eligibility</h2>
                    <p>You are eligible for a refund or replacement if an item arrives damaged or spoiled, you receive a wrong item, an item is missing from your order, or we cannot fulfil an item you paid for. Report the issue within <strong>24 hours of delivery</strong>.</p>
                    <h2 id="how-to-request">2. How to request a refund</h2>
                    <p>Contact us on WhatsApp (055 123 4567) or email <a href="mailto:hello@fenroy.com" class="text-brand-dark-red hover:underline">hello@fenroy.com</a> with your order number and a brief description. A photo helps us resolve it faster. We'll confirm within a few hours during store hours.</p>
                    <h2 id="timeline">3. Refund timeline</h2>
                    <p>Approved refunds are returned to the original payment method — Mobile Money or card — within <strong>1–5 business days</strong>. MoMo refunds are typically faster. We'll notify you by SMS or email when the refund is processed.</p>
                    <h2 id="exceptions">4. Exceptions</h2>
                    <p>We cannot refund items that have been consumed, items reported after the 24-hour window, or price changes after your order was confirmed. Change-of-mind returns are not accepted for fresh or perishable goods.</p>
                </div>
            </article>

            {{-- Delivery Policy --}}
            <article x-show="policy === 'delivery'">
                <h1 class="text-[32px] font-extrabold tracking-[-0.02em] text-brand-text leading-tight mb-2">Delivery Policy</h1>
                <p class="text-[13px] text-brand-muted mb-6">Last updated 14 August 2026 · Applies to fenroy.com and the Fenroy storefront</p>
                <div class="bg-[#F9F9F9] rounded-xl px-5 py-4 mb-8 text-[14px] text-brand-secondary-text leading-[1.7]">
                    <strong class="text-brand-text">In short:</strong> we deliver same-day across Accra if you order by 6pm. Fees vary by zone. Free delivery applies above a spend threshold. Minimum basket GH₵ 50.
                </div>
                <div class="prose-policy">
                    <h2 id="zones">1. Delivery zones</h2>
                    <p>We deliver to <strong>Osu, Labone, Cantonments, East Legon, Airport Residential, Spintex, Teshie, Achimota, Dansoman, Tema and Kasoa</strong>. Message us on WhatsApp if your area isn't listed — we're expanding.</p>
                    <h2 id="windows">2. Delivery windows & cut-off</h2>
                    <p>Choose at checkout: <strong>Morning (9am–12pm)</strong>, <strong>Afternoon (12pm–3pm)</strong>, or <strong>Evening (4pm–7pm)</strong>. For same-day delivery, order by <strong>6:00pm</strong>. Orders placed after the cut-off are scheduled for the next available slot.</p>
                    <h2 id="fees">3. Fees & free delivery</h2>
                    <p>Delivery fees range from GH₵ 10 to GH₵ 35 depending on your zone. Free delivery applies when your basket meets the zone threshold (GH₵ 250–GH₵ 400). A minimum basket of <strong>GH₵ 50</strong> applies to all orders.</p>
                    <h2 id="failed">4. Failed delivery</h2>
                    <p>Our rider calls 10 minutes before arrival. If no one is available and we cannot reach you, we'll attempt delivery once more. If the second attempt fails, the order is returned and a partial refund (minus the delivery fee) is processed.</p>
                </div>
            </article>

        </div>
    </div>
</div>
</x-layouts.storefront>
