<div
    x-data="{
        show: $wire.entangle('showFormModal'),
        isEditing: @js((bool) $editingWishId),
    }"
    @open-create-modal.window="
        isEditing = false;
        $wire.editingWishId = null;
        $wire.title = '';
        $wire.price = '';
        $wire.url = '';
        $wire.description = '';
        show = true;
    "
    @open-edit-modal.window="
        isEditing = true;
        $wire.editingWishId = $event.detail.id;
        $wire.title = $event.detail.title;
        $wire.price = $event.detail.price;
        $wire.url = $event.detail.url;
        $wire.description = $event.detail.description;
        show = true;
    "
    x-show="show"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="if (show) { show = false; $wire.closeFormModal(); }"
>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        {{-- Backdrop --}}
        <div
            @click="show = false; $wire.closeFormModal()"
            x-show="show"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        {{-- Modal Dialog --}}
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-left shadow-2xl transition-all w-full sm:my-8 sm:max-w-lg p-6 sm:p-8"
        >
            <div class="flex items-center justify-between pb-4 border-b border-neutral-200 dark:border-neutral-800">
                <h3
                    class="text-xl font-bold text-neutral-900 dark:text-neutral-100"
                    x-text="isEditing ? @js(__('Edit Wish')) : @js(__('Add a New Wish'))"
                >
                    {{ $editingWishId ? __('Edit Wish') : __('Add a New Wish') }}
                </h3>
                <button
                    type="button"
                    @click="show = false; $wire.closeFormModal()"
                    class="p-1 rounded-lg text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit="saveWish" class="mt-6 space-y-4">
                {{-- Title --}}
                <div>
                    <label for="wish-title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        {{ __('Title') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="wish-title"
                        wire:model="title"
                        placeholder="{{ __('e.g. Sony WH-1000XM5 Headphones') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base sm:text-sm"
                        autofocus
                    >
                    @error('title')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Price & Link Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="wish-price" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                            {{ __('Price (kr)') }}
                        </label>
                        <input
                            type="text"
                            id="wish-price"
                            inputmode="decimal"
                            wire:model="price"
                            placeholder="{{ __('e.g. 2499.00') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base sm:text-sm"
                        >
                        @error('price')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="wish-url" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                            {{ __('Link / URL') }}
                        </label>
                        <input
                            type="url"
                            id="wish-url"
                            wire:model="url"
                            placeholder="https://..."
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base sm:text-sm"
                        >
                        @error('url')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label for="wish-description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        {{ __('Description / Notes') }}
                    </label>
                    <textarea
                        id="wish-description"
                        wire:model="description"
                        rows="3"
                        placeholder="{{ __('Add details like color, size, where to buy, or specific preferences...') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-base sm:text-sm resize-none"
                    ></textarea>
                    @error('description')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                    <button
                        type="button"
                        @click="show = false; $wire.closeFormModal()"
                        class="px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition cursor-pointer"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-700 dark:text-blue-300 text-sm font-medium hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:text-blue-800 dark:hover:text-blue-200 disabled:opacity-50 transition cursor-pointer data-loading:opacity-75"
                    >
                        <span
                            wire:loading.remove
                            wire:target="saveWish"
                            x-text="isEditing ? @js(__('Update Wish')) : @js(__('Save Wish'))"
                        >
                            {{ $editingWishId ? __('Update Wish') : __('Save Wish') }}
                        </span>
                        <span wire:loading wire:target="saveWish">
                            {{ __('Saving...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
