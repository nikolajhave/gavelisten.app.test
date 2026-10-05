<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="min-w-0 flex-1">
        @php
            $owner = $this->wishlist->user ?? auth()->user();
            $ownerName = $owner?->name;
        @endphp
        @if ($ownerName)
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">
                {{ $ownerName }}
            </p>
        @endif
        @if ($isEditingWishlistTitle)
            <form wire:submit="saveWishlistTitle" class="flex flex-wrap items-center gap-2">
                <div class="flex-1 min-w-[220px] max-w-md">
                    <input
                        type="text"
                        wire:model="wishlistTitle"
                        wire:keydown.escape="cancelEditingWishlistTitle"
                        placeholder="{{ __('Wishlist title') }}"
                        class="w-full text-xl sm:text-2xl font-bold px-3.5 py-1.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition shadow-xs"
                        autofocus
                    >
                    @error('wishlistTitle')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center gap-1.5">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shadow-xs cursor-pointer data-loading:opacity-75"
                        title="{{ __('Save') }}"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ __('Save') }}</span>
                    </button>
                    <button
                        type="button"
                        wire:click="cancelEditingWishlistTitle"
                        class="inline-flex items-center px-3 py-2 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-medium transition cursor-pointer"
                        title="{{ __('Cancel') }}"
                    >
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        @else
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                    {{ $this->wishlist->title }}
                </h1>
                <button
                    type="button"
                    wire:click="startEditingWishlistTitle"
                    class="p-1.5 rounded-xl text-neutral-400 hover:text-neutral-900 dark:text-neutral-500 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition cursor-pointer"
                    title="{{ __('Edit wishlist title') }}"
                >
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                    {{ trans_choice(':count wish|:count wishes', $this->wishes->count()) }}
                </span>
            </div>
        @endif
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            {{ __('Drag and drop to reorder, and share your list with family & friends.') }}
        </p>
    </div>

    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
        <button
            type="button"
            @click="$dispatch('open-create-modal')"
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium text-sm hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition shadow-xs cursor-pointer data-loading:opacity-75"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ __('Add Wish') }}</span>
        </button>

        @include('livewire.wishlist-manager.share-dropdown')
    </div>
</div>
