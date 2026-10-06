<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="min-w-0 flex-1">
        @php
            $ownerName = $this->wishlist->user?->name;
        @endphp
        @if ($ownerName)
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">
                {{ $ownerName }}
            </p>
        @endif
        <div class="flex items-center gap-2.5 flex-wrap">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                {{ $this->wishlist->title }}
            </h1>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                {{ trans_choice(':count wish|:count wishes', $this->wishes->count()) }}
            </span>
        </div>

        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            {{ __('Shared wishlist — explore wishes and follow links to find or purchase the gifts.') }}
        </p>
    </div>

    @include('livewire.public-wishlist.header-actions')
</div>
