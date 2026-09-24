<div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] flex flex-col justify-between">
    <div>
        {{-- Navigation Bar --}}
        <header class="border-b border-neutral-200 dark:border-neutral-800 bg-white/70 dark:bg-neutral-900/70 backdrop-blur-md sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center text-blue-700 dark:text-blue-300 transition group-hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V4a2 2 0 112 2h-2zm0 0V4a2 2 0 10-2 2h2m0 0H4a2 2 0 00-2 2v3a2 2 0 002 2h16a2 2 0 002-2V10a2 2 0 00-2-2h-4" />
                        </svg>
                    </div>
                    <span class="font-bold text-lg tracking-tight text-neutral-900 dark:text-white">Gavelisten</span>
                </a>

                <div class="flex items-center gap-3">
                    @auth
                        <a
                            href="{{ route('wishlist') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium text-sm hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shadow-xs"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>{{ __('My Wishlist') }}</span>
                        </a>

                        {{-- Friends Burger Menu --}}
                        <div class="relative" x-data="{ open: false }">
                            <button
                                type="button"
                                @click="open = !open"
                                class="inline-flex items-center justify-center p-2 rounded-xl border border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-900 text-sm font-medium transition cursor-pointer"
                                title="{{ __('Friends') }}"
                                aria-label="{{ __('Friends') }}"
                                :aria-expanded="open.toString()"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                class="fixed sm:absolute inset-x-4 sm:inset-x-auto sm:left-auto sm:right-0 top-24 sm:top-full sm:mt-2 mx-auto sm:mx-0 w-auto sm:w-80 max-w-sm sm:max-w-none bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-neutral-800 py-3 z-50 divide-y divide-neutral-100 dark:divide-neutral-800"
                                style="display: none;"
                            >
                                <div class="px-4 py-2 flex items-center justify-between">
                                    <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        {{ __('Friends') }}
                                    </h3>
                                    @if ($this->friends->isNotEmpty())
                                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                                            {{ $this->friends->count() }}
                                        </span>
                                    @endif
                                </div>

                                <div class="py-1 max-h-60 overflow-y-auto">
                                    @if ($this->friends->isEmpty())
                                        <div class="px-4 py-6 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                            <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400 dark:text-neutral-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <p class="font-medium text-neutral-900 dark:text-neutral-100">{{ __('No friends yet') }}</p>
                                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                                {{ __('When viewing a shared wishlist, click "Add friend" to add them to your list.') }}
                                            </p>
                                        </div>
                                    @else
                                        @foreach ($this->friends as $friend)
                                            @php
                                                $friendWishlist = $friend->wishlists->first();
                                                $friendName = $friend->name ?: __('Friend');
                                                $initial = mb_substr($friendName, 0, 1);
                                            @endphp
                                            <div
                                                wire:key="public-friend-item-{{ $friend->id }}"
                                                class="flex items-center justify-between gap-2 px-3 py-2 hover:bg-neutral-50 dark:hover:bg-neutral-800/60 rounded-xl mx-1 transition group"
                                            >
                                                @if ($friendWishlist)
                                                    <a
                                                        href="{{ route('wishlist.public', $friendWishlist->share_token) }}"
                                                        class="flex items-center gap-3 min-w-0 flex-1"
                                                    >
                                                        <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-semibold text-xs flex items-center justify-center shrink-0">
                                                            {{ strtoupper($initial) }}
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate group-hover:underline">
                                                                {{ $friendName }}
                                                            </p>
                                                            <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                                                                {{ $friendWishlist->title }}
                                                            </p>
                                                        </div>
                                                    </a>
                                                @else
                                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                                        <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-semibold text-xs flex items-center justify-center shrink-0">
                                                            {{ strtoupper($initial) }}
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                                                {{ $friendName }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif

                                                <button
                                                    type="button"
                                                    wire:click="removeFriend({{ $friend->id }})"
                                                    class="opacity-0 group-hover:opacity-100 focus:opacity-100 p-1 rounded-lg text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition cursor-pointer"
                                                    title="{{ __('Remove friend') }}"
                                                    aria-label="{{ __('Remove friend') }}"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Add Friend Section at the bottom --}}
                                <div class="pt-3 px-3">
                                    <div class="mb-2 flex items-center justify-between">
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                            <span>{{ __('Add friend') }}</span>
                                        </h4>
                                        @if ($friendSearchQuery !== '')
                                            <button
                                                type="button"
                                                wire:click="clearFriendSearch"
                                                class="text-[11px] font-medium text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition cursor-pointer"
                                            >
                                                {{ __('Clear') }}
                                            </button>
                                        @endif
                                    </div>

                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-neutral-400 dark:text-neutral-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.250ms="friendSearchQuery"
                                            placeholder="{{ __('Search name, email or phone...') }}"
                                            class="w-full pl-9 pr-8 py-2 rounded-xl text-base sm:text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition"
                                        >
                                        @if ($friendSearchQuery !== '')
                                            <button
                                                type="button"
                                                wire:click="clearFriendSearch"
                                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition cursor-pointer"
                                                title="{{ __('Clear') }}"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>

                                    @if ($friendSearchQuery !== '')
                                        <div class="mt-2.5 max-h-48 overflow-y-auto space-y-1">
                                            @if ($this->friendSearchResults->isEmpty())
                                                <div class="py-3 px-2 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                                    {{ __('No users found') }}
                                                </div>
                                            @else
                                                @foreach ($this->friendSearchResults as $resultUser)
                                                    @php
                                                        $isAlreadyFriend = $this->friends->contains('id', $resultUser->id);
                                                        $resultName = $resultUser->name ?: __('Friend');
                                                        $resultInitial = mb_substr($resultName, 0, 1);
                                                        $resultWishlist = $resultUser->wishlists->first();
                                                    @endphp
                                                    <div
                                                        wire:key="public-search-result-{{ $resultUser->id }}"
                                                        class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white dark:bg-neutral-800/80 border border-neutral-100 dark:border-neutral-700/50 hover:border-neutral-200 dark:hover:border-neutral-700 transition"
                                                    >
                                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                            <div class="w-7 h-7 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-semibold text-xs flex items-center justify-center shrink-0">
                                                                {{ strtoupper($resultInitial) }}
                                                            </div>
                                                            <div class="min-w-0 flex-1">
                                                                <p class="text-xs font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                                                    {{ $resultName }}
                                                                </p>
                                                                @if ($resultWishlist)
                                                                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate">
                                                                        {{ $resultWishlist->title }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div>
                                                            @if ($isAlreadyFriend)
                                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-medium bg-neutral-100 dark:bg-neutral-700 text-neutral-500 dark:text-neutral-400">
                                                                    <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                    <span>{{ __('Friend added') }}</span>
                                                                </span>
                                                            @else
                                                                <button
                                                                    type="button"
                                                                    wire:click="addFriend({{ $resultUser->id }})"
                                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition cursor-pointer shrink-0"
                                                                >
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                                    </svg>
                                                                    <span>{{ __('Add friend') }}</span>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium text-sm hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shadow-xs"
                        >
                            <span>{{ __('Create Your Wishlist') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Main Container --}}
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            {{-- Wishlist Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-neutral-200 dark:border-neutral-800">
                <div>
                    @php
                        $ownerName = $this->wishlist->user?->name;
                    @endphp
                    @if ($ownerName)
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">
                            {{ $ownerName }}
                        </p>
                    @endif
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">
                            {{ $this->wishlist->title }}
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                            {{ trans_choice(':count wish|:count wishes', $this->wishes->count()) }}
                        </span>
                    </div>
                    <p class="text-sm sm:text-base text-neutral-500 dark:text-neutral-400 mt-2">
                        {{ __('Shared wishlist — explore wishes and follow links to find or purchase the gifts.') }}
                    </p>
                </div>

                <div x-data="{ copied: false, shareUrl: window.location.href }" class="flex items-center gap-2 shrink-0 flex-wrap">
                    @auth
                        @if (! $this->isOwner)
                            @if ($this->isFriend)
                                <button
                                    type="button"
                                    wire:click="removeFriend"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-emerald-300 dark:border-emerald-800/80 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-sm font-medium transition cursor-pointer shadow-xs"
                                    title="{{ __('Remove friend') }}"
                                >
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ __('Friend added') }}</span>
                                </button>
                            @else
                                <button
                                    type="button"
                                    wire:click="addFriend"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 text-sm font-medium transition cursor-pointer shadow-xs"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    <span>{{ __('Add friend') }}</span>
                                </button>
                            @endif
                        @endif
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 text-sm font-medium transition cursor-pointer shadow-xs"
                            title="{{ __('Sign in to add friend') }}"
                        >
                            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span>{{ __('Add friend') }}</span>
                        </a>
                    @endauth

                    <button
                        type="button"
                        @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2500)"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 text-sm font-medium transition cursor-pointer shadow-xs"
                    >
                        <svg x-show="!copied" class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        <svg x-show="copied" style="display: none;" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="copied ? '{{ __('Link Copied!') }}' : '{{ __('Share Wishlist') }}'"></span>
                    </button>
                </div>
            </div>

            {{-- Card Grid / Empty State --}}
            <div class="mt-8 sm:mt-10">
                @if ($this->wishes->isEmpty())
                    <div class="text-center py-16 px-4 bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-3xl">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400 dark:text-neutral-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('No wishes on this list yet') }}</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 max-w-sm mx-auto">
                            {{ __("The owner hasn't added any wishes to this list yet. Check back later!") }}
                        </p>
                    </div>
                @else
                    {{-- Clean Card Grid Layout --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($this->wishes as $wish)
                            <article
                                wire:key="public-wish-{{ $wish->id }}"
                                class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-6 shadow-xs hover:shadow-md hover:border-neutral-300 dark:hover:border-neutral-700 transition flex flex-col group"
                            >
                                <div class="space-y-3">
                                    {{-- Title & Price --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <h2 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 leading-snug group-hover:text-neutral-950 dark:group-hover:text-white transition">
                                            {{ $wish->title }}
                                        </h2>

                                        <div class="flex items-center gap-4 shrink-0">
                                            @if ($wish->formatted_price)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-300">
                                                    {{ $wish->formatted_price }}
                                                </span>
                                            @endif

                                            @if ($wish->url)
                                                <a
                                                    href="{{ $wish->url }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="inline-flex items-center justify-center p-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200/80 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shadow-xs group/link"
                                                    title="{{ __('See Product') }}"
                                                    aria-label="{{ __('See Product') }}"
                                                >
                                                    <svg class="w-4 h-4 shrink-0 transition-transform group-hover/link:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    @if ($wish->description)
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400 whitespace-pre-line leading-relaxed line-clamp-4">{{ $wish->description }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </main>
    </div>

    {{-- Footer --}}
    <footer class="mt-16 border-t border-neutral-200 dark:border-neutral-800 py-8 bg-neutral-50/50 dark:bg-neutral-950/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-500 dark:text-neutral-400">
            <p>
                {{ __('Powered by') }} <a href="{{ route('home') }}" class="font-semibold hover:text-neutral-900 dark:hover:text-white transition">Gavelisten</a> — {{ __('Easy wishlist sharing for every occasion.') }}
            </p>
            <p>
                <a href="{{ route('login') }}" class="underline hover:text-neutral-900 dark:hover:text-white transition">{{ __('Create your own wishlist for free') }}</a>
            </p>
        </div>
    </footer>
</div>
