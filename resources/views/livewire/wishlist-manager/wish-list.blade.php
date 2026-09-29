<div class="mt-8">
    @if ($this->wishes->isEmpty())
        <div
            wire:key="wishlist-empty-state"
            class="text-center py-16 px-4 bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-3xl"
        >
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400 dark:text-neutral-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('No wishes yet') }}</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 max-w-sm mx-auto">
                {{ __("Your wishlist is empty. Start adding gifts you'd love to receive!") }}
            </p>
            <button
                type="button"
                @click="$dispatch('open-create-modal')"
                wire:click="openCreateModal"
                class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 font-medium text-sm hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Add Your First Wish') }}</span>
            </button>
        </div>
    @else
        {{-- Livewire 4 wire:sort for drag & drop --}}
        <div
            wire:key="wishlist-items-list"
            wire:sort="reorderWishes"
            x-sort:config="{ fallbackOnBody: true }"
            class="space-y-3"
        >
            @foreach ($this->wishes as $wish)
                @include('livewire.wishlist-manager.wish-item', ['wish' => $wish])
            @endforeach
        </div>
    @endif
</div>
