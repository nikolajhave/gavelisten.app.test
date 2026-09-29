<article
    wire:key="public-wish-{{ $wish->id }}"
    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl sm:rounded-3xl p-4 sm:p-5 shadow-xs hover:shadow-md hover:border-neutral-300 dark:hover:border-neutral-700 transition flex flex-col group"
>
    <div class="space-y-2.5 sm:space-y-3">
        {{-- Title & Price --}}
        <div class="flex items-start justify-between gap-3">
            <h2 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-neutral-100 leading-snug group-hover:text-neutral-950 dark:group-hover:text-white transition">
                {{ $wish->title }}
            </h2>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @if ($wish->formatted_price)
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg sm:rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-300">
                        {{ $wish->formatted_price }}
                    </span>
                @endif

                @if ($wish->url)
                    <a
                        href="{{ $wish->url }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center justify-center p-1.5 rounded-lg sm:rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200/80 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shadow-xs group/link"
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
            <p class="text-sm text-neutral-600 dark:text-neutral-400 whitespace-pre-line leading-relaxed">{{ $wish->description }}</p>
        @endif
    </div>
</article>
