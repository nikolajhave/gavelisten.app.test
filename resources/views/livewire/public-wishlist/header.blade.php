<div>
    @php
        $ownerName = $this->wishlist->user?->name;
    @endphp
    @if ($ownerName)
        <p class="text-base font-medium text-neutral-500 dark:text-neutral-400 mb-1">
            {{ $ownerName }}
        </p>
    @endif
    <div class="flex items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">
                {{ $this->wishlist->title }}
            </h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                {{ trans_choice(':count wish|:count wishes', $this->wishes->count()) }}
            </span>
        </div>

        @include('livewire.public-wishlist.header-actions')
    </div>

    <p class="text-sm sm:text-base text-neutral-500 dark:text-neutral-400 mt-2">
        {{ __('Shared wishlist — explore wishes and follow links to find or purchase the gifts.') }}
    </p>
</div>
