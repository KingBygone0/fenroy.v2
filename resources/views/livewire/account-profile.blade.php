<div class="space-y-5">

    {{-- Avatar / identity card --}}
    <div class="bg-white rounded-2xl border border-brand-border-light p-6">
        <div class="flex items-center gap-4">

            {{-- Avatar circle --}}
            <div class="relative shrink-0 group" style="width:64px; height:64px;">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $name }}"
                         class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-brand-light-red flex items-center justify-center text-xl font-bold text-brand-dark-red">
                        {{ $initials }}
                    </div>
                @endif

                {{-- Loading spinner over avatar while uploading --}}
                <div wire:loading wire:target="photo"
                     class="absolute inset-0 rounded-full bg-black/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-lg font-bold text-brand-text truncate">{{ $name }}</p>
                <p class="text-sm text-brand-secondary-text truncate">{{ $email }}</p>
            </div>

            {{-- Hidden file input --}}
            <input type="file" wire:model="photo" id="photo-upload" accept="image/*"
                   class="hidden">

            <button type="button"
                    onclick="document.getElementById('photo-upload').click()"
                    class="text-sm text-brand-red cursor-pointer hover:underline shrink-0">
                Edit photo
            </button>
        </div>

        @error('photo')
            <p class="mt-2 text-xs font-medium text-brand-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Personal details form --}}
    <div class="bg-white rounded-2xl border border-brand-border-light p-6">
        <h2 class="text-[17px] font-extrabold text-brand-text mb-5">Personal details</h2>

        {{-- Full name --}}
        <div class="mb-4">
            <label class="block text-[13px] font-semibold text-brand-text mb-1.5" for="profile-name">Full name</label>
            <input
                id="profile-name"
                type="text"
                wire:model.blur="name"
                autocomplete="name"
                class="w-full h-11 px-4 rounded-[10px] border border-brand-input-border focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition text-sm text-brand-text"
            >
            @error('name')
                <p class="mt-1.5 text-xs font-medium text-brand-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-4">
            <label class="block text-[13px] font-semibold text-brand-text mb-1.5" for="profile-email">Email address</label>
            <input
                id="profile-email"
                type="email"
                wire:model.blur="email"
                autocomplete="email"
                class="w-full h-11 px-4 rounded-[10px] border border-brand-input-border focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition text-sm text-brand-text"
            >
            @error('email')
                <p class="mt-1.5 text-xs font-medium text-brand-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div class="mb-4">
            <label class="block text-[13px] font-semibold text-brand-text mb-1.5" for="profile-phone">Phone number</label>
            <input
                id="profile-phone"
                type="tel"
                wire:model.blur="phone"
                autocomplete="tel"
                class="w-full h-11 px-4 rounded-[10px] border border-brand-input-border focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition text-sm text-brand-text"
            >
            @error('phone')
                <p class="mt-1.5 text-xs font-medium text-brand-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Current password — shown only when email is being changed --}}
        <div class="mb-4" x-data='{ originalEmail: {!! json_encode(auth()->user()?->email, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!} }' x-show="$wire.email !== originalEmail">
            <label class="block text-[13px] font-semibold text-brand-text mb-1.5" for="profile-current-password">
                Current password
                <span class="font-normal text-brand-secondary-text">(required to change email)</span>
            </label>
            <input
                id="profile-current-password"
                type="password"
                wire:model="current_password"
                autocomplete="current-password"
                placeholder="Enter your current password"
                class="w-full h-11 px-4 rounded-[10px] border border-brand-input-border focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition text-sm text-brand-text"
            >
            @error('current_password')
                <p class="mt-1.5 text-xs font-medium text-brand-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Save button --}}
        <div class="flex justify-end mt-2">
            <button
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="h-11 px-7 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors cursor-pointer disabled:opacity-45 flex items-center gap-2"
            >
                <svg wire:loading wire:target="save" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span wire:loading wire:target="save">Saving&hellip;</span>
                <span wire:loading.remove wire:target="save">Save changes</span>
            </button>
        </div>
    </div>

    {{-- Delete account --}}
    <div class="bg-white rounded-2xl border border-brand-border-light p-6" x-data="{ open: false }" id="delete-account">
        <h2 class="text-[17px] font-extrabold text-brand-text mb-2">Delete account</h2>
        <p class="text-sm text-brand-secondary-text mb-4">
            This permanently deletes your Fenroy account, saved addresses, wishlist and reviews.
            Past orders are kept for our accounts with your name and contact details removed. This can't be undone.
        </p>

        <button type="button" x-show="!open" @click="open = true"
                class="h-11 px-6 rounded-full border border-brand-danger text-brand-danger text-sm font-semibold hover:bg-brand-danger hover:text-white transition-colors cursor-pointer">
            Delete my account
        </button>

        <div x-show="open" style="display:none" class="space-y-4">
            @if(auth()->user()?->password)
                <div>
                    <label class="block text-[13px] font-semibold text-brand-text mb-1.5" for="delete-password">Current password</label>
                    <input id="delete-password" type="password" wire:model="deletePassword" autocomplete="current-password"
                           class="w-full h-11 px-4 rounded-[10px] border border-brand-input-border focus:outline-none focus:border-brand-danger focus:ring-[3px] focus:ring-brand-danger/20 transition text-sm text-brand-text">
                    @error('deletePassword') <p class="mt-1.5 text-xs font-medium text-brand-danger">{{ $message }}</p> @enderror
                </div>
            @else
                <div>
                    <label class="block text-[13px] font-semibold text-brand-text mb-1.5" for="delete-confirm">Type <strong>DELETE</strong> to confirm</label>
                    <input id="delete-confirm" type="text" wire:model="deleteConfirm" autocomplete="off"
                           class="w-full h-11 px-4 rounded-[10px] border border-brand-input-border focus:outline-none focus:border-brand-danger focus:ring-[3px] focus:ring-brand-danger/20 transition text-sm text-brand-text">
                    @error('deleteConfirm') <p class="mt-1.5 text-xs font-medium text-brand-danger">{{ $message }}</p> @enderror
                </div>
            @endif

            @error('deleteAccount') <p class="text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p> @enderror

            <div class="flex flex-wrap gap-3">
                <button type="button" wire:click="deleteAccount" wire:loading.attr="disabled"
                        class="h-11 px-6 rounded-full bg-brand-danger text-white text-sm font-semibold hover:opacity-90 transition cursor-pointer disabled:opacity-45">
                    <span wire:loading.remove wire:target="deleteAccount">Permanently delete account</span>
                    <span wire:loading wire:target="deleteAccount">Deleting&hellip;</span>
                </button>
                <button type="button" @click="open = false"
                        class="h-11 px-6 rounded-full border border-brand-border text-brand-text text-sm font-semibold cursor-pointer">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
