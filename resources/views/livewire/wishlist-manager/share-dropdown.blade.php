<div class="relative" x-data="{ open: false, copied: false, shareUrl: '{{ url('/w/' . $this->wishlist->share_token) }}' }">
    <button
        type="button"
        @click="open = !open"
        class="inline-flex items-center justify-center w-9.5 h-9.5 sm:w-10.5 sm:h-10.5 rounded-xl border border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-900 transition cursor-pointer"
        title="{{ __('Share Wishlist') }}"
        aria-label="{{ __('Share Wishlist') }}"
        :aria-expanded="open.toString()"
    >
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
