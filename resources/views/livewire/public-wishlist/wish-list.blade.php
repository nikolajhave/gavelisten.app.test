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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($this->wishes as $wish)
                @include('livewire.public-wishlist.wish-card', ['wish' => $wish])
            @endforeach
        </div>
    @endif
</div>
