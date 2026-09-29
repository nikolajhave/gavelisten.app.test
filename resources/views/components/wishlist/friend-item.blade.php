@props(['friend'])

@php
    $friendWishlist = $friend->wishlists->first();
    $friendName = $friend->name ?: __('Friend');
    $initial = mb_substr($friendName, 0, 1);
@endphp
<div
    {{ $attributes->merge(['class' => 'flex items-center justify-between gap-2 px-3 py-2 hover:bg-neutral-50 dark:hover:bg-neutral-800/60 rounded-xl mx-1 transition group']) }}
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
