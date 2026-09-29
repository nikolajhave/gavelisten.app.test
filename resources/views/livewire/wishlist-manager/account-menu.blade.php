<div class="relative" x-data="{ open: false }">
    <button
        type="button"
        @click="open = !open"
        class="inline-flex items-center justify-center w-9.5 h-9.5 sm:w-10.5 sm:h-10.5 rounded-xl border border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-900 transition cursor-pointer"
        title="{{ __('Menu') }}"
        aria-label="{{ __('Menu') }}"
        :aria-expanded="open.toString()"
    >
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Mobile Backdrop --}}
    <div
        x-show="open"
        @click="open = false"
        class="fixed inset-0 bg-neutral-900/40 backdrop-blur-xs z-40 sm:hidden"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
    ></div>

    <div
        x-show="open"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed sm:absolute inset-x-4 sm:inset-x-auto sm:left-auto sm:right-0 top-24 sm:top-full sm:mt-2 mx-auto sm:mx-0 w-auto sm:w-80 max-w-sm sm:max-w-none bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-neutral-800 pt-4 pb-3 z-50 divide-y divide-neutral-100 dark:divide-neutral-800"
        style="display: none;"
    >
        {{-- User Summary --}}
        @php
            $currentUser = auth()->user();
            $currentUserName = $currentUser?->name ?: __('Your Profile');
            $currentUserInitial = mb_substr($currentUserName, 0, 1);
            $currentUserContact = $currentUser?->email ?: $currentUser?->phone;
        @endphp
        <div class="px-4 pb-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-semibold text-xs flex items-center justify-center shrink-0">
                    {{ strtoupper($currentUserInitial) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                        {{ $currentUserName }}
                    </p>
                    @if ($currentUserContact)
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate">
                            {{ $currentUserContact }}
                        </p>
                    @endif
                </div>
            </div>
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
        </div>

        @include('livewire.wishlist-manager.friends-panel')

        {{-- Footer Section with Sign out --}}
        <div class="pt-2 px-1 space-y-1">
            <button
                type="button"
                @click="$dispatch('open-changelog'); openMenu = false"
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
        </div>
    </div>
</div>
