<x-wishlist.account-menu :user="auth()->user()">
    <x-slot:profileAction>
        <button
            type="button"
            @click="open = false; $wire.showProfileModal = true"
            wire:click="openProfileModal"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition cursor-pointer shrink-0"
            title="{{ __('Edit Profile') }}"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <span>{{ __('Profile') }}</span>
        </button>
    </x-slot:profileAction>

    @if ($this->availableWishlists->count() > 1)
        <div class="py-3">
            <div class="px-4 pb-2 flex items-center justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                    {{ __('Wishlists') }}
                </h3>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                    {{ $this->availableWishlists->count() }}
                </span>
            </div>
            <div class="px-2 space-y-1 max-h-48 overflow-y-auto overscroll-contain">
                @foreach ($this->availableWishlists as $list)
                    @php
                        $isActive = ($list->id === $this->wishlist->id);
                        $isOwn = ($list->user_id === auth()->id());
                    @endphp
                    <button
                        type="button"
                        wire:click="selectWishlist({{ $list->id }})"
                        @click="open = false"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-medium transition cursor-pointer {{ $isActive ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-semibold' : 'text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800' }}"
                        wire:key="wishlist-switch-{{ $list->id }}"
                    >
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-blue-600 dark:text-blue-400' : 'text-neutral-400 dark:text-neutral-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="truncate">
                                {{ $list->title }}
                                @if (! $isOwn && $list->user)
                                    <span class="text-xs font-normal opacity-75">({{ $list->user->name }})</span>
                                @endif
                            </span>
                        </div>
                        @if ($isActive)
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @include('livewire.wishlist-manager.friends-panel')

    <x-slot:footer>
        <button
            type="button"
            @click="$dispatch('open-changelog'); open = false"
            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition cursor-pointer"
        >
            <svg class="w-4 h-4 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ __("What's new?") }}</span>
        </button>
        <form method="POST" action="{{ route('logout') }}" class="block">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition cursor-pointer"
            >
                <svg class="w-4 h-4 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>{{ __('Sign out') }}</span>
            </button>
        </form>
    </x-slot:footer>
</x-wishlist.account-menu>
