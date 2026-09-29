<div x-data="{ copied: false, shareUrl: window.location.href }" class="flex items-center gap-2 shrink-0">
    @auth
        @if (! $this->isOwner)
            @if ($this->isFriend)
                <button
                    type="button"
                    wire:click="removeFriend"
                    class="inline-flex items-center justify-center gap-2 p-2.5 sm:px-4 sm:py-2.5 rounded-xl border border-emerald-300 dark:border-emerald-800/80 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-sm font-medium transition cursor-pointer shadow-xs"
                    title="{{ __('Remove friend') }}"
                    aria-label="{{ __('Remove friend') }}"
                >
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('Friend added') }}</span>
                </button>
            @else
                <button
                    type="button"
                    wire:click="addFriend"
                    class="inline-flex items-center justify-center gap-2 p-2.5 sm:px-4 sm:py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 text-sm font-medium transition cursor-pointer shadow-xs"
                    title="{{ __('Add friend') }}"
                    aria-label="{{ __('Add friend') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('Add friend') }}</span>
                </button>
            @endif
        @endif
    @else
        <a
            href="{{ route('login') }}"
            class="inline-flex items-center justify-center gap-2 p-2.5 sm:px-4 sm:py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 text-sm font-medium transition cursor-pointer shadow-xs"
            title="{{ __('Sign in to add friend') }}"
            aria-label="{{ __('Sign in to add friend') }}"
        >
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span class="hidden sm:inline">{{ __('Add friend') }}</span>
        </a>
    @endauth

    <button
        type="button"
        @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2500)"
        class="inline-flex items-center justify-center gap-2 p-2.5 sm:px-4 sm:py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200 text-sm font-medium transition cursor-pointer shadow-xs"
        title="{{ __('Share Wishlist') }}"
        aria-label="{{ __('Share Wishlist') }}"
        :title="copied ? '{{ __('Link Copied!') }}' : '{{ __('Share Wishlist') }}'"
        :aria-label="copied ? '{{ __('Link Copied!') }}' : '{{ __('Share Wishlist') }}'"
    >
        <svg x-show="!copied" class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
        </svg>
        <svg x-show="copied" style="display: none;" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="hidden sm:inline" x-text="copied ? '{{ __('Link Copied!') }}' : '{{ __('Share Wishlist') }}'"></span>
    </button>
</div>
