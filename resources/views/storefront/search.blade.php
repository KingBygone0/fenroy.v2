<x-layouts.storefront title="{{ request('q') ? 'Search: '.request('q').' — Fenroy' : 'Search — Fenroy' }}">
    <livewire:search-page :q="request('q', '')" />
</x-layouts.storefront>
