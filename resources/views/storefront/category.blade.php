<x-layouts.storefront :title="($categoryName ?? 'Category') . ' — Fenroy'">
    <livewire:category-page :slug="$slug ?? 'fruits-vegetables'" />
</x-layouts.storefront>
