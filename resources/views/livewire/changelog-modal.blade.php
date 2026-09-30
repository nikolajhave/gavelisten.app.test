<div>
    @if ($changelog)
        <div
            x-data="{ show: $wire.entangle('showModal') }"
            @open-changelog.window="show = true"
            x-show="show"
            x-cloak
            style="display: none;"
            class="relative z-50"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-changelog-title"
        >
            {{-- Backdrop --}}
            <div
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity"
                @click="show = false; $wire.dismiss()"
            ></div>

            {{-- Modal container --}}
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div
                        x-show="show"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        @click.stop
                        @keydown.escape.window="if (show) { show = false; $wire.dismiss(); }"
                        class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-neutral-900 p-6 text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-neutral-100 dark:border-neutral-800"
                    >
                        {{-- Close button in top right --}}
                        <div class="absolute right-5 top-5">
                            <button
                                type="button"
                                wire:click="dismiss"
                                @click="show = false"
                                class="rounded-xl p-1.5 text-neutral-400 hover:text-neutral-700 dark:text-neutral-500 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition cursor-pointer"
                                aria-label="{{ __('Close') }}"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Header / Badge --}}
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/80 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                                    {{ __("What's new?") }}
                                </span>
                                @if ($changelog->version)
                                    <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300">
                                        {{ $changelog->version }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Title & Date --}}
                        <div class="pr-6">
                            <h3 id="modal-changelog-title" class="text-xl font-bold text-neutral-900 dark:text-neutral-100 tracking-tight leading-snug">
                                {{ $changelog->title }}
                            </h3>
                            @if ($changelog->published_at)
                                <time class="text-xs text-neutral-400 dark:text-neutral-500 block mt-1">
                                    {{ $changelog->published_at->translatedFormat('j. F Y') }}
                                </time>
                            @endif
                        </div>

                        {{-- Body / Content --}}
                        <div class="py-2 max-h-[60vh] overflow-y-auto text-sm text-neutral-600 dark:text-neutral-300 changelog-content">
                            {!! $changelog->formatted_content !!}
                        </div>

                        {{-- Footer button --}}
                        <div class="mt-6">
                            <button
                                type="button"
                                wire:click="dismiss"
                                @click="show = false"
                                class="w-full py-3 px-4 rounded-xl bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-100 text-white dark:text-neutral-900 font-semibold text-sm transition shadow-sm cursor-pointer flex items-center justify-center gap-2"
                            >
                                <span>{{ __('Got it!') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
