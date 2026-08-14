@props(['active' => ''])

{{-- Mobile horizontal tab bar --}}
<nav class="md:hidden flex overflow-x-auto gap-1 pb-4 -mx-4 px-4 mb-5" style="scrollbar-width:none">
    @php
    $mobileItems = [
        ['route' => 'account.profile',   'label' => 'Profile',    'key' => 'profile'],
        ['route' => 'account.orders',    'label' => 'My Orders',  'key' => 'orders'],
        ['route' => 'account.addresses', 'label' => 'Addresses',  'key' => 'addresses'],
        ['route' => 'account.wishlist',  'label' => 'Wishlist',   'key' => 'wishlist'],
    ];
    @endphp
    @foreach($mobileItems as $item)
        <a href="{{ route($item['route']) }}"
           class="shrink-0 h-10 min-h-[44px] px-5 rounded-full text-sm font-semibold transition-colors whitespace-nowrap
               {{ $active === $item['key']
                   ? 'bg-brand-text text-white'
                   : 'bg-white border border-brand-border-light text-brand-secondary-text hover:text-brand-text' }}">
            {{ $item['label'] }}
        </a>
    @endforeach

    {{-- Mobile logout --}}
    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
        @csrf
        <button type="submit"
                class="h-10 min-h-[44px] px-5 rounded-full text-sm font-semibold whitespace-nowrap bg-white border border-brand-border-light text-brand-danger hover:bg-brand-light-red transition-colors">
            Sign out
        </button>
    </form>
</nav>

<nav class="hidden md:block w-56 shrink-0">
    <div class="bg-white rounded-2xl border border-brand-border-light overflow-hidden">

        @php
        $items = [
            [
                'route' => 'account.profile',
                'label' => 'Profile',
                'key'   => 'profile',
                'icon'  => 'user',
            ],
            [
                'route' => 'account.orders',
                'label' => 'My Orders',
                'key'   => 'orders',
                'icon'  => 'document',
            ],
            [
                'route' => 'account.addresses',
                'label' => 'Addresses',
                'key'   => 'addresses',
                'icon'  => 'map-pin',
            ],
            [
                'route' => 'account.wishlist',
                'label' => 'Wishlist',
                'key'   => 'wishlist',
                'icon'  => 'heart',
            ],
        ];
        @endphp

        @foreach ($items as $item)
            @php
                $isActive = $active === $item['key'];
            @endphp
            <a
                href="{{ route($item['route']) }}"
                class="flex items-center gap-3 py-3.5 text-sm font-medium border-b border-brand-border-light transition-colors min-h-[44px]
                    {{ $isActive
                        ? 'text-brand-red font-bold bg-brand-light-red/50 border-l-2 border-brand-red pl-[14px] pr-4'
                        : 'text-brand-secondary-text hover:text-brand-text hover:bg-[#FAFAFA] px-4' }}"
            >
                @if ($item['icon'] === 'user')
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                @elseif ($item['icon'] === 'document')
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"/></svg>
                @elseif ($item['icon'] === 'map-pin')
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                @elseif ($item['icon'] === 'heart')
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                @endif

                {{ $item['label'] }}
            </a>
        @endforeach

        {{-- Desktop logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3.5 text-sm font-medium text-brand-danger hover:bg-[#FFF0F0] transition-colors min-h-[44px]">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1"/>
                </svg>
                Sign out
            </button>
        </form>

    </div>
</nav>
