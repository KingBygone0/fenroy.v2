<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-base font-bold text-brand-text">Saved addresses</h2>
        <button wire:click="$toggle('showForm')"
                class="inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add address
        </button>
    </div>

    {{-- Add form --}}
    @if($showForm)
    <div class="bg-white border border-brand-border-light rounded-2xl p-5">
        <h3 class="text-sm font-bold text-brand-text mb-4">New delivery address</h3>
        <form wire:submit="save" class="space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-brand-text mb-1">Full name *</label>
                    <input wire:model="full_name" type="text" placeholder="Kofi Mensah"
                           class="w-full h-10 px-3 rounded-xl border border-brand-border-light text-sm focus:outline-none focus:ring-2 focus:ring-brand-light-red">
                    @error('full_name') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-brand-text mb-1">Phone *</label>
                    <input wire:model="phone" type="tel" placeholder="0244 000 000"
                           class="w-full h-10 px-3 rounded-xl border border-brand-border-light text-sm focus:outline-none focus:ring-2 focus:ring-brand-light-red">
                    @error('phone') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-brand-text mb-1">Street / area *</label>
                <input wire:model="line1" type="text" placeholder="14 Palm Avenue, East Legon"
                       class="w-full h-10 px-3 rounded-xl border border-brand-border-light text-sm focus:outline-none focus:ring-2 focus:ring-brand-light-red">
                @error('line1') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-brand-text mb-1">City *</label>
                    <input wire:model="city" type="text" class="w-full h-10 px-3 rounded-xl border border-brand-border-light text-sm focus:outline-none focus:ring-2 focus:ring-brand-light-red">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-brand-text mb-1">Region *</label>
                    <input wire:model="region" type="text" class="w-full h-10 px-3 rounded-xl border border-brand-border-light text-sm focus:outline-none focus:ring-2 focus:ring-brand-light-red">
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input wire:model="is_default" type="checkbox" class="w-4 h-4 rounded accent-brand-red">
                <span class="text-sm text-brand-text">Set as default address</span>
            </label>
            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="h-10 px-5 bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold rounded-xl transition-colors">
                    Save address
                </button>
                <button type="button" wire:click="$set('showForm', false)"
                        class="h-10 px-5 bg-[#F5F5F5] hover:bg-[#EAEAEA] text-brand-text text-sm font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Address list --}}
    @forelse($addresses as $addr)
    <div class="bg-white border border-brand-border-light rounded-2xl p-5 flex items-start gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-sm font-bold text-brand-text">{{ $addr['full_name'] }}</span>
                @if($addr['is_default'])
                <span class="inline-flex items-center h-5 px-2 rounded-full bg-brand-light-red text-brand-dark-red text-[10px] font-bold">Default</span>
                @endif
            </div>
            <p class="text-sm text-brand-secondary-text">{{ $addr['line1'] }}, {{ $addr['city'] }}, {{ $addr['region'] }}</p>
            <p class="text-xs text-brand-muted mt-0.5">{{ $addr['phone'] }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @unless($addr['is_default'])
            <button wire:click="setDefault({{ $addr['id'] }})"
                    class="text-xs text-brand-red hover:underline font-medium cursor-pointer">
                Set default
            </button>
            @endunless
            <button wire:click="delete({{ $addr['id'] }})"
                    wire:confirm="Delete this address?"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-brand-muted hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </button>
        </div>
    </div>
    @empty
    <div class="text-center py-12 text-brand-secondary-text">
        <svg class="w-10 h-10 mx-auto mb-3 text-brand-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <p class="text-sm font-medium">No addresses saved yet.</p>
        <p class="text-xs text-brand-muted mt-1">Add one to speed up checkout.</p>
    </div>
    @endforelse

</div>
