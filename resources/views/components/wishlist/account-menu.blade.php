@props(['user'])

<div x-data="{ open: false }" {{ $attributes->merge(['class' => 'relative']) }}>
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
        x-show="open"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed sm:absolute inset-x-4 sm:inset-x-auto sm:left-auto sm:right-0 top-24 sm:top-full sm:mt-2 mx-auto sm:mx-0 w-auto sm:w-80 max-w-sm sm:max-w-none bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-neutral-800 pt-4 pb-3 z-50 divide-y divide-neutral-100 dark:divide-neutral-800"
        style="display: none;"
    >
        {{-- User Summary --}}
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
            {{ $profileAction }}
        </div>

        {{ $slot }}

        <div class="pt-2 px-1 space-y-1">
            {{ $footer }}
        </div>
    </div>
</div>
