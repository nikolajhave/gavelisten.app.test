@php
    $deleteConfirmationText = __('Are you sure you want to delete :title? This action cannot be undone.', ['title' => '___TITLE___']);
    [$deletePrefix, $deleteSuffix] = str_contains($deleteConfirmationText, '___TITLE___')
        ? explode('___TITLE___', $deleteConfirmationText, 2)
        : [$deleteConfirmationText, ''];
@endphp
<div
    x-data="{
        show: $wire.entangle('showDeleteModal'),
        wishTitle: @js($deletingWishTitle ?? ''),
    }"
    @open-delete-modal.window="show = true; wishTitle = $event.detail.title"
    x-show="show"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="if (show) { show = false; $wire.closeDeleteModal(); }"
>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div
            @click="show = false; $wire.closeDeleteModal()"
            x-show="show"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-left shadow-2xl transition-all w-full sm:my-8 sm:max-w-md p-6 sm:p-7"
        >
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-950/60 flex items-center justify-center shrink-0 text-red-600 dark:text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                        {{ __('Delete Wish') }}
                    </h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                        {{ $deletePrefix }}<span x-text="wishTitle ? '&quot;' + wishTitle + '&quot;' : @js($deletingWishTitle ? '"' . $deletingWishTitle . '"' : '')">@if($deletingWishTitle)"{{ $deletingWishTitle }}"@endif</span>{{ $deleteSuffix }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button
                    type="button"
                    @click="show = false; $wire.closeDeleteModal()"
                    class="px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition cursor-pointer"
                >
                    {{ __('Cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="deleteWish"
                    wire:loading.attr="disabled"
                    class="px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700 disabled:opacity-50 transition cursor-pointer data-loading:opacity-75"
                >
                    <span wire:loading.remove wire:target="deleteWish">
                        {{ __('Delete') }}
                    </span>
                    <span wire:loading wire:target="deleteWish">
                        {{ __('Deleting...') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
