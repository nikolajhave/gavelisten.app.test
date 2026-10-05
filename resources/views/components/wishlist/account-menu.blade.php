@props(['user'])

<div
    x-data="{
        open: false,
        isDragging: false,
        startY: 0,
        currentY: 0,
        offsetY: 0,
        startTime: 0,
        init() {
            this.$watch('open', value => {
                if (!value) {
                    this.isDragging = false;
                    this.offsetY = 0;
                }
            });
        },
        startTouch(e) {
            if (window.innerWidth >= 640) return;
            const target = e.target;
            const isHandle = target.closest('[data-drag-handle]') !== null;
            const drawer = this.$refs.drawer;
            if (isHandle || (drawer && drawer.scrollTop <= 0)) {
                this.isDragging = true;
                this.startY = e.touches[0].clientY;
                this.currentY = this.startY;
                this.offsetY = 0;
                this.startTime = Date.now();
            }
        },
        moveTouch(e) {
            if (!this.isDragging) return;
            const clientY = e.touches[0].clientY;
            const deltaY = clientY - this.startY;
            if (deltaY > 0) {
                this.offsetY = deltaY;
                if (e.cancelable) {
                    e.preventDefault();
                }
            } else {
                this.offsetY = 0;
            }
        },
        endTouch(e) {
            if (!this.isDragging) return;
            const elapsed = Date.now() - this.startTime;
            const velocity = this.offsetY / (elapsed || 1);
            const shouldClose = this.offsetY > 70 || (this.offsetY > 30 && velocity > 0.5);
            this.isDragging = false;
            if (shouldClose) {
                this.open = false;
            }
            this.offsetY = 0;
        }
    }"
    {{ $attributes->merge(['class' => 'relative']) }}
>
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
        x-ref="drawer"
        x-show="open"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        @touchstart="startTouch($event)"
        @touchmove="moveTouch($event)"
        @touchend="endTouch($event)"
        @touchcancel="endTouch($event)"
        x-transition:enter="transition ease-out duration-200 sm:duration-100"
        x-transition:enter-start="translate-y-full opacity-0 sm:translate-y-0 sm:scale-95 sm:opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100 sm:opacity-100"
        x-transition:leave="transition ease-in duration-150 sm:duration-75"
        x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100 sm:opacity-100"
        x-transition:leave-end="translate-y-full opacity-0 sm:translate-y-0 sm:scale-95 sm:opacity-0"
        :class="{ 'transition-transform duration-200 ease-out': !isDragging }"
        :style="isDragging && offsetY > 0 ? 'transform: translateY(' + offsetY + 'px) !important;' : ''"
        class="fixed sm:absolute inset-x-0 sm:inset-x-auto bottom-0 sm:bottom-auto sm:left-auto sm:right-0 sm:top-full sm:mt-2 mx-auto sm:mx-0 w-full sm:w-80 max-h-[85vh] sm:max-h-[calc(100vh-6rem)] overflow-y-auto overscroll-contain bg-white dark:bg-neutral-900 rounded-t-3xl sm:rounded-2xl shadow-2xl sm:shadow-xl border-t sm:border border-neutral-200 dark:border-neutral-800 pt-2 sm:pt-4 pb-8 sm:pb-3 z-50 divide-y divide-neutral-100 dark:divide-neutral-800"
        style="display: none;"
    >
        {{-- Mobile Drag Handle --}}
        <div
            data-drag-handle
            class="w-full py-2 flex items-center justify-center cursor-grab active:cursor-grabbing touch-none sm:hidden shrink-0"
        >
            <div class="w-12 h-1.5 rounded-full bg-neutral-300 dark:bg-neutral-700"></div>
        </div>

        {{-- User Summary & Header Actions --}}
        @php
            $currentUserName = $user?->name ?: __('Your Profile');
            $currentUserInitial = mb_substr($currentUserName, 0, 1);
            $currentUserContact = $user?->email ?: $user?->phone;
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

            <div class="flex items-center gap-2 shrink-0">
                {{ $profileAction }}

                <button
                    type="button"
                    @click="open = false"
                    class="p-1 rounded-lg text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition cursor-pointer sm:hidden"
                    title="{{ __('Close') }}"
                    aria-label="{{ __('Close') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{ $slot }}

        <div class="pt-2 px-1 space-y-1">
            {{ $footer }}
        </div>
    </div>
</div>
