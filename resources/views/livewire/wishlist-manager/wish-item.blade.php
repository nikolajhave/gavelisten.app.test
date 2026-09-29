@php
    $wishPriceFormatted = $wish->price !== null ? str_replace('.', ',', (string) $wish->price) : '';
    $hasDetails = (bool) ($wish->description || $wish->url);
@endphp
<div
    wire:sort:item="{{ $wish->id }}"
    wire:key="wish-{{ $wish->id }}"
    @click="$dispatch('open-edit-modal', { id: {{ $wish->id }}, title: @js($wish->title), price: @js($wishPriceFormatted), url: @js($wish->url ?? ''), description: @js($wish->description ?? '') })"
    wire:click="openEditModal({{ $wish->id }})"
    class="group relative grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-x-3 gap-y-2 sm:gap-x-4 sm:gap-y-1.5 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700 rounded-2xl p-2.5 sm:p-5 shadow-xs transition cursor-pointer"
>
    {{-- Col 1: Drag Handle --}}
    <button
        type="button"
        wire:sort:handle
        @click.stop
        class="col-start-1 row-start-1 {{ $hasDetails ? 'sm:row-span-2' : '' }} self-center p-1.5 rounded-lg text-neutral-400 hover:text-neutral-700 dark:text-neutral-500 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-grab active:cursor-grabbing transition shrink-0 touch-none select-none"
        title="{{ __('Drag to reorder') }}"
    >
        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
        </svg>
    </button>

    {{-- Col 2 (xs: Col 2 & 3): Header & Price --}}
    <div class="col-start-2 col-end-4 sm:col-end-3 row-start-1 flex flex-wrap items-center gap-x-3 gap-y-1 min-w-0 self-center">
        <button
            type="button"
            @click.stop="$dispatch('open-edit-modal', { id: {{ $wish->id }}, title: @js($wish->title), price: @js($wishPriceFormatted), url: @js($wish->url ?? ''), description: @js($wish->description ?? '') })"
            wire:click.stop="openEditModal({{ $wish->id }})"
            class="font-semibold text-neutral-900 dark:text-neutral-100 text-base text-left hover:text-neutral-600 dark:hover:text-neutral-300 hover:underline underline-offset-2 transition cursor-pointer focus:outline-none"
            title="{{ __('Edit wish') }}"
        >
            {{ $wish->title }}
        </button>

        @if ($wish->formatted_price)
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-300 shrink-0">
                {{ $wish->formatted_price }}
            </span>
        @endif
    </div>

    {{-- Col 2: Text (Description) & Link (URL) --}}
    @if ($hasDetails)
        <div class="col-start-2 col-end-3 row-start-2 min-w-0 space-y-1.5 sm:space-y-2">
            @if ($wish->description)
                <p class="text-sm text-neutral-600 dark:text-neutral-400 whitespace-pre-line line-clamp-2">{{ $wish->description }}</p>
            @endif

            @if ($wish->url)
                <div class="max-w-full">
                    <a
                        href="{{ $wish->url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click.stop
                        class="inline-flex items-center gap-1.5 max-w-full text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition group/link"
                    >
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span class="truncate underline underline-offset-2">{{ $wish->url_domain }}</span>
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- Col 3: Actions --}}
    <div class="col-start-3 row-start-2 sm:row-start-1 {{ $hasDetails ? 'sm:row-span-2' : '' }} flex items-center justify-self-end gap-1 shrink-0 self-center">
        <button
            type="button"
            @click.stop="$dispatch('open-edit-modal', { id: {{ $wish->id }}, title: @js($wish->title), price: @js($wishPriceFormatted), url: @js($wish->url ?? ''), description: @js($wish->description ?? '') })"
            wire:click.stop="openEditModal({{ $wish->id }})"
            class="p-2 rounded-xl text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition cursor-pointer"
            title="{{ __('Edit wish') }}"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
        </button>
        <button
            type="button"
            @click.stop="$dispatch('open-delete-modal', { id: {{ $wish->id }}, title: @js($wish->title) })"
            wire:click.stop="confirmDeleteWish({{ $wish->id }})"
            class="p-2 rounded-xl text-neutral-500 hover:text-red-600 dark:text-neutral-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 transition cursor-pointer"
            title="{{ __('Delete wish') }}"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
    </div>
</div>
