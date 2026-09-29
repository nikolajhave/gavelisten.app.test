@props([
    'friends',
    'query',
    'results',
    'keyPrefix' => '',
])

<div {{ $attributes->merge(['class' => 'pt-3 px-3']) }}>
    <div class="mb-2 flex items-center justify-between">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>{{ __('Find and add friend') }}</span>
        </h4>
        @if ($query !== '')
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
        @if ($query !== '')
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

    @if ($query !== '')
        <div class="mt-2.5 max-h-48 overflow-y-auto space-y-1">
            @if ($results->isEmpty())
                <div class="py-3 px-2 text-center text-xs text-neutral-500 dark:text-neutral-400">
                    {{ __('No users found') }}
                </div>
            @else
                @foreach ($results as $resultUser)
                    @php
                        $isAlreadyFriend = $friends->contains('id', $resultUser->id);
                        $resultName = $resultUser->name ?: __('Friend');
                        $resultInitial = mb_substr($resultName, 0, 1);
                        $resultWishlist = $resultUser->wishlists->first();
                    @endphp
                    <div
                        wire:key="{{ $keyPrefix }}search-result-{{ $resultUser->id }}"
                        class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white dark:bg-neutral-800/80 border border-neutral-100 dark:border-neutral-700/50 hover:border-neutral-200 dark:hover:border-neutral-700 transition"
                    >
                        @if ($resultWishlist)
                            <a
                                href="{{ route('wishlist.public', $resultWishlist->share_token) }}"
                                class="flex items-center gap-2.5 min-w-0 flex-1 group"
                            >
                                <div class="w-7 h-7 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-semibold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper($resultInitial) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-neutral-900 dark:text-neutral-100 truncate group-hover:underline">
                                        {{ $resultName }}
                                    </p>
                                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate">
                                        {{ $resultWishlist->title }}
                                    </p>
                                </div>
                            </a>
                        @else
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-semibold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper($resultInitial) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                        {{ $resultName }}
                                    </p>
                                </div>
                            </div>
                        @endif

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
