<div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-neutral-200 dark:border-neutral-800">
            <div class="min-w-0 flex-1">
                @php
                    $owner = $this->wishlist->user ?? auth()->user();
                    $ownerName = $owner?->name;
                @endphp
                @if ($ownerName)
                    <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">
                        {{ $ownerName }}
                    </p>
                @endif
                @if ($isEditingWishlistTitle)
                    <form wire:submit="saveWishlistTitle" class="flex flex-wrap items-center gap-2">
                        <div class="flex-1 min-w-[220px] max-w-md">
                            <input
                                type="text"
                                wire:model="wishlistTitle"
                                wire:keydown.escape="cancelEditingWishlistTitle"
                                placeholder="{{ __('Wishlist title') }}"
                                class="w-full text-xl sm:text-2xl font-bold px-3.5 py-1.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition shadow-xs"
                                autofocus
                            >
                            @error('wishlistTitle')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shadow-xs cursor-pointer data-loading:opacity-75"
                                title="{{ __('Save') }}"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ __('Save') }}</span>
                            </button>
                            <button
                                type="button"
                                wire:click="cancelEditingWishlistTitle"
                                class="inline-flex items-center px-3 py-2 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-medium transition cursor-pointer"
                                title="{{ __('Cancel') }}"
                            >
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                            {{ $this->wishlist->title }}
                        </h1>
                        <button
                            type="button"
                            wire:click="startEditingWishlistTitle"
                            class="p-1.5 rounded-xl text-neutral-400 hover:text-neutral-900 dark:text-neutral-500 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition cursor-pointer"
                            title="{{ __('Edit wishlist title') }}"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                            {{ trans_choice(':count wish|:count wishes', $this->wishes->count()) }}
                        </span>
                    </div>
                @endif
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                    {{ __('Drag and drop to reorder, and share your list with family & friends.') }}
                </p>
            </div>

            <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-2 sm:gap-3">
                <div class="flex items-center gap-2 sm:gap-3">
                    <button
                        type="button"
                        wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium text-sm hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shadow-xs cursor-pointer data-loading:opacity-75"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('Add Wish') }}</span>
                    </button>

                    {{-- Share Wishlist Dropdown --}}
                    <div class="relative" x-data="{ open: false, copied: false, shareUrl: '{{ url('/w/' . $this->wishlist->share_token) }}' }">
                        <button
                            type="button"
                            @click="open = !open"
                            class="inline-flex items-center justify-center p-2 sm:px-3 sm:py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-900 text-sm font-medium transition cursor-pointer"
                            title="{{ __('Share Wishlist') }}"
                            aria-label="{{ __('Share Wishlist') }}"
                            :aria-expanded="open.toString()"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
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
                            class="fixed sm:absolute inset-x-4 sm:inset-x-auto sm:left-auto sm:right-0 top-24 sm:top-full sm:mt-2 mx-auto sm:mx-0 w-auto sm:w-80 max-w-sm sm:max-w-none bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-neutral-800 p-4 z-50 divide-y divide-neutral-100 dark:divide-neutral-800"
                            style="display: none;"
                        >
                            <div class="pb-3">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                        {{ __('Share Wishlist') }}
                                    </h3>
                                </div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                    {{ __('Anyone with this link can view your wishlist.') }}
                                </p>
                            </div>

                            <div class="pt-3 space-y-2.5">
                                <div class="flex items-center gap-1.5 p-1.5 bg-neutral-50 dark:bg-neutral-800/80 rounded-xl border border-neutral-200 dark:border-neutral-700/60">
                                    <span class="text-xs font-mono text-neutral-600 dark:text-neutral-300 px-2 truncate select-all flex-1" x-text="shareUrl"></span>
                                    <button
                                        type="button"
                                        @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2500)"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shrink-0 cursor-pointer"
                                    >
                                        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <svg x-show="copied" style="display: none;" class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy Link') }}'"></span>
                                    </button>
                                </div>

                                <a
                                    href="{{ route('wishlist.public', $this->wishlist->share_token) }}"
                                    target="_blank"
                                    class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-xl text-xs font-medium border border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    <span>{{ __('See Public Wishlist') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Friends Burger Menu --}}
                    <div class="relative" x-data="{ open: false }">
                        <button
                            type="button"
                            @click="open = !open"
                            class="inline-flex items-center justify-center p-2 sm:px-3 sm:py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-900 text-sm font-medium transition cursor-pointer"
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

                            <div class="py-1 max-h-72 overflow-y-auto">
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
                                            wire:key="friend-item-{{ $friend->id }}"
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
                        </div>
                    </div>
                </div>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center p-2 sm:px-3 sm:py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-900 text-sm font-medium transition cursor-pointer"
                        title="{{ __('Sign out') }}"
                        aria-label="{{ __('Sign out') }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Wishlist Items Container --}}
        <div class="mt-8">
            @if ($this->wishes->isEmpty())
                <div
                    wire:key="wishlist-empty-state"
                    class="text-center py-16 px-4 bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-3xl"
                >
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400 dark:text-neutral-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('No wishes yet') }}</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 max-w-sm mx-auto">
                        {{ __("Your wishlist is empty. Start adding gifts you'd love to receive!") }}
                    </p>
                    <button
                        type="button"
                        wire:click="openCreateModal"
                        class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium text-sm hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('Add Your First Wish') }}</span>
                    </button>
                </div>
            @else
                {{-- Livewire 4 wire:sort for drag & drop --}}
                <div
                    wire:key="wishlist-items-list"
                    wire:sort="reorderWishes"
                    class="space-y-3"
                >
                    @foreach ($this->wishes as $wish)
                        <div
                            wire:sort:item="{{ $wish->id }}"
                            wire:key="wish-{{ $wish->id }}"
                            class="group relative flex items-start sm:items-center justify-between gap-4 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700 rounded-2xl p-4 sm:p-5 shadow-xs transition"
                        >
                            <div class="flex items-start sm:items-center gap-3.5 min-w-0 flex-1">
                                {{-- Drag Handle --}}
                                <button
                                    type="button"
                                    wire:sort:handle
                                    class="mt-1 sm:mt-0 p-1.5 rounded-lg text-neutral-400 hover:text-neutral-700 dark:text-neutral-500 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-grab active:cursor-grabbing transition"
                                    title="{{ __('Drag to reorder') }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                    </svg>
                                </button>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <button
                                            type="button"
                                            wire:click="openEditModal({{ $wish->id }})"
                                            class="font-semibold text-neutral-900 dark:text-neutral-100 text-base text-left hover:text-neutral-600 dark:hover:text-neutral-300 hover:underline underline-offset-2 transition cursor-pointer focus:outline-none"
                                            title="{{ __('Edit wish') }}"
                                        >
                                            {{ $wish->title }}
                                        </button>

                                        @if ($wish->formatted_price)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-300">
                                                {{ $wish->formatted_price }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($wish->description)
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2 whitespace-pre-line line-clamp-2">{{ $wish->description }}</p>
                                    @endif

                                    @if ($wish->url)
                                        <div class="mt-2">
                                            <a
                                                href="{{ $wish->url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition group/link"
                                            >
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span class="truncate max-w-xs sm:max-w-md underline underline-offset-2">{{ $wish->url }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 shrink-0">
                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $wish->id }})"
                                    class="p-2 rounded-xl text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition cursor-pointer"
                                    title="{{ __('Edit wish') }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="confirmDeleteWish({{ $wish->id }})"
                                    class="p-2 rounded-xl text-neutral-500 hover:text-red-600 dark:text-neutral-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 transition cursor-pointer"
                                    title="{{ __('Delete wish') }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Create / Edit Modal (Leveraging wire:show for zero layout shift) --}}
    <div
        wire:show="showFormModal"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            {{-- Backdrop --}}
            <div
                wire:click="closeFormModal"
                class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
            ></div>

            {{-- Modal Dialog --}}
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 sm:p-8">
                <div class="flex items-center justify-between pb-4 border-b border-neutral-200 dark:border-neutral-800">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ $editingWishId ? __('Edit Wish') : __('Add a New Wish') }}
                    </h3>
                    <button
                        type="button"
                        wire:click="closeFormModal"
                        class="p-1 rounded-lg text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveWish" class="mt-6 space-y-4">
                    {{-- Title --}}
                    <div>
                        <label for="wish-title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                            {{ __('Title') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="wish-title"
                            wire:model="title"
                            placeholder="{{ __('e.g. Sony WH-1000XM5 Headphones') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-sm"
                            autofocus
                        >
                        @error('title')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Price & Link Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="wish-price" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                {{ __('Price (kr)') }}
                            </label>
                            <input
                                type="text"
                                id="wish-price"
                                wire:model="price"
                                placeholder="{{ __('e.g. 2499.00') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-sm"
                            >
                            @error('price')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="wish-url" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                {{ __('Link / URL') }}
                            </label>
                            <input
                                type="url"
                                id="wish-url"
                                wire:model="url"
                                placeholder="https://..."
                                class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-sm"
                            >
                            @error('url')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="wish-description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                            {{ __('Description / Notes') }}
                        </label>
                        <textarea
                            id="wish-description"
                            wire:model="description"
                            rows="3"
                            placeholder="{{ __('Add details like color, size, where to buy, or specific preferences...') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-sm resize-none"
                        ></textarea>
                        @error('description')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                        <button
                            type="button"
                            wire:click="closeFormModal"
                            class="px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 text-sm font-medium hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 disabled:opacity-50 transition cursor-pointer data-loading:opacity-75"
                        >
                            <span wire:loading.remove wire:target="saveWish">
                                {{ $editingWishId ? __('Update Wish') : __('Save Wish') }}
                            </span>
                            <span wire:loading wire:target="saveWish">
                                {{ __('Saving...') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal (Leveraging wire:show) --}}
    <div
        wire:show="showDeleteModal"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                wire:click="closeDeleteModal"
                class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
            ></div>

            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-7">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-950/60 flex items-center justify-center shrink-0 text-red-600 dark:text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                            {{ __('Delete Wish') }}
                        </h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            {{ __('Are you sure you want to delete :title? This action cannot be undone.', ['title' => '"' . $deletingWishTitle . '"']) }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition cursor-pointer"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="deleteWish"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700 disabled:opacity-50 transition cursor-pointer data-loading:opacity-75"
                    >
                        <span wire:loading.remove wire:target="deleteWish">
                            {{ __('Delete') }}
                        </span>
                        <span wire:loading wire:target="deleteWish">
                            {{ __('Deleting...') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
