<div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] flex flex-col justify-between">
    <div>
        {{-- Navigation Bar --}}
        <header class="border-b border-neutral-200 dark:border-neutral-800 bg-white/70 dark:bg-neutral-900/70 backdrop-blur-md sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-neutral-900 dark:bg-white flex items-center justify-center text-white dark:text-neutral-900 transition group-hover:scale-105">
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
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 font-medium text-sm hover:bg-neutral-800 dark:hover:bg-neutral-100 transition shadow-xs"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>{{ __('My Wishlist') }}</span>
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 font-medium text-sm hover:bg-neutral-800 dark:hover:bg-neutral-100 transition shadow-xs"
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

                <div x-data="{ copied: false, shareUrl: window.location.href }" class="flex items-center gap-2 shrink-0">
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
                                class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-6 shadow-xs hover:shadow-md hover:border-neutral-300 dark:hover:border-neutral-700 transition flex flex-col justify-between group"
                            >
                                <div class="space-y-3">
                                    {{-- Title & Price --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <h2 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 leading-snug group-hover:text-neutral-950 dark:group-hover:text-white transition">
                                            {{ $wish->title }}
                                        </h2>

                                        @if ($wish->price !== null)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-300 shrink-0">
                                                {{ number_format((float) $wish->price, 2, ',', '.') }} {{ __('kr.') }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Description --}}
                                    @if ($wish->description)
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400 whitespace-pre-line leading-relaxed line-clamp-4">
                                            {{ $wish->description }}
                                        </p>
                                    @endif
                                </div>

                                {{-- External Link --}}
                                @if ($wish->url)
                                    <div class="mt-6 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                                        <a
                                            href="{{ $wish->url }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex items-center justify-between w-full px-4 py-2.5 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-sm font-medium hover:bg-neutral-800 dark:hover:bg-neutral-100 transition shadow-xs group/btn"
                                        >
                                            <span class="truncate">{{ __('See Product') }}</span>
                                            <svg class="w-4 h-4 shrink-0 transition-transform group-hover/btn:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </div>
                                @endif
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
