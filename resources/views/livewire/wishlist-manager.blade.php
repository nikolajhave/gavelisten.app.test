<div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-neutral-200 dark:border-neutral-800">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                        {{ $this->wishlist->title }}
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                        {{ $this->wishes->count() }} {{ \Illuminate\Support\Str::plural('wish', $this->wishes->count()) }}
                    </span>
                </div>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                    Manage your wishes, drag and drop to reorder, and share your list with family & friends.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 font-medium text-sm hover:bg-neutral-800 dark:hover:bg-neutral-100 transition shadow-xs cursor-pointer data-loading:opacity-75"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Wish</span>
                </button>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center px-3 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-900 text-sm font-medium transition cursor-pointer"
                        title="Sign out"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Share Token Bar --}}
        <div x-data="{ copied: false, shareUrl: '{{ url('/w/' . $this->wishlist->share_token) }}' }" class="mt-6 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 text-sm text-neutral-600 dark:text-neutral-400 min-w-0">
                <svg class="w-4 h-4 shrink-0 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
                <span class="font-medium text-neutral-700 dark:text-neutral-300 shrink-0">Public Share Link:</span>
                <span class="truncate font-mono text-xs text-neutral-500 select-all" x-text="shareUrl"></span>
            </div>
            <button
                type="button"
                @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2500)"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-neutral-300 dark:border-neutral-700 hover:bg-white dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 transition shrink-0 cursor-pointer"
            >
                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <svg x-show="copied" style="display: none;" class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
            </button>
        </div>

        {{-- Wishlist Items Container --}}
        <div class="mt-8">
            @if ($this->wishes->isEmpty())
                <div class="text-center py-16 px-4 bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-3xl">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400 dark:text-neutral-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">No wishes yet</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 max-w-sm mx-auto">
                        Your wishlist is empty. Start adding gifts you'd love to receive!
                    </p>
                    <button
                        type="button"
                        wire:click="openCreateModal"
                        class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 font-medium text-sm hover:bg-neutral-800 dark:hover:bg-neutral-100 transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Your First Wish</span>
                    </button>
                </div>
            @else
                {{-- Livewire 4 wire:sort for drag & drop --}}
                <div
                    wire:sort="reorderWishes"
                    class="space-y-3"
                >
                    @foreach ($this->wishes as $wish)
                        <div
                            wire:sort:item="{{ $wish->id }}"
                            wire:key="wish-{{ $wish->id }}"
                            class="group relative flex items-start sm:items-center justify-between gap-4 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-700 rounded-2xl p-4 sm:p-5 shadow-xs transition"
                        >
                            <div class="flex items-start sm:items-center gap-3.5 min-w-0 flex-1">
                                {{-- Drag Handle --}}
                                <button
                                    type="button"
                                    wire:sort:handle
                                    class="mt-1 sm:mt-0 p-1.5 rounded-lg text-neutral-400 hover:text-neutral-700 dark:text-neutral-500 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-grab active:cursor-grabbing transition"
                                    title="Drag to reorder"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                    </svg>
                                </button>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 text-base">
                                            {{ $wish->title }}
                                        </h3>

                                        @if ($wish->price !== null)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-300">
                                                {{ number_format((float) $wish->price, 2, ',', '.') }} kr.
                                            </span>
                                        @endif
                                    </div>

                                    @if ($wish->description)
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1 whitespace-pre-line line-clamp-2">
                                            {{ $wish->description }}
                                        </p>
                                    @endif

                                    @if ($wish->url)
                                        <div class="mt-2">
                                            <a
                                                href="{{ $wish->url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition group/link"
                                            >
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span class="truncate max-w-xs sm:max-w-md underline underline-offset-2">{{ $wish->url }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 shrink-0">
                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $wish->id }})"
                                    class="p-2 rounded-xl text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-neutral-800 transition cursor-pointer"
                                    title="Edit wish"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="confirmDeleteWish({{ $wish->id }})"
                                    class="p-2 rounded-xl text-neutral-500 hover:text-red-600 dark:text-neutral-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 transition cursor-pointer"
                                    title="Delete wish"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Create / Edit Modal (Leveraging wire:show for zero layout shift) --}}
    <div
        wire:show="showFormModal"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            {{-- Backdrop --}}
            <div
                wire:click="closeFormModal"
                class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
            ></div>

            {{-- Modal Dialog --}}
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 sm:p-8">
                <div class="flex items-center justify-between pb-4 border-b border-neutral-200 dark:border-neutral-800">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ $editingWishId ? 'Edit Wish' : 'Add a New Wish' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="closeFormModal"
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
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="wish-title"
                            wire:model="title"
                            placeholder="e.g. Sony WH-1000XM5 Headphones"
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-sm"
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
                                Price (kr.)
                            </label>
                            <input
                                type="text"
                                id="wish-price"
                                wire:model="price"
                                placeholder="e.g. 2499.00"
                                class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-sm"
                            >
                            @error('price')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="wish-url" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Link / URL
                            </label>
                            <input
                                type="url"
                                id="wish-url"
                                wire:model="url"
                                placeholder="https://..."
                                class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-sm"
                            >
                            @error('url')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="wish-description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                            Description / Notes
                        </label>
                        <textarea
                            id="wish-description"
                            wire:model="description"
                            rows="3"
                            placeholder="Add details like color, size, where to buy, or specific preferences..."
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white transition text-sm resize-none"
                        ></textarea>
                        @error('description')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                        <button
                            type="button"
                            wire:click="closeFormModal"
                            class="px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-sm font-medium hover:bg-neutral-800 dark:hover:bg-neutral-100 disabled:opacity-50 transition cursor-pointer data-loading:opacity-75"
                        >
                            <span wire:loading.remove wire:target="saveWish">
                                {{ $editingWishId ? 'Update Wish' : 'Save Wish' }}
                            </span>
                            <span wire:loading wire:target="saveWish">
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal (Leveraging wire:show) --}}
    <div
        wire:show="showDeleteModal"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                wire:click="closeDeleteModal"
                class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
            ></div>

            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-7">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-950/60 flex items-center justify-center shrink-0 text-red-600 dark:text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                            Delete Wish
                        </h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Are you sure you want to delete <span class="font-semibold text-neutral-800 dark:text-neutral-200">"{{ $deletingWishTitle }}"</span>? This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="deleteWish"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700 disabled:opacity-50 transition cursor-pointer data-loading:opacity-75"
                    >
                        <span wire:loading.remove wire:target="deleteWish">
                            Delete
                        </span>
                        <span wire:loading wire:target="deleteWish">
                            Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
