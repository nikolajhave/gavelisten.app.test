<header class="border-b border-neutral-200 dark:border-neutral-800 sticky top-0 z-30 relative">
    <div class="absolute inset-0 bg-white/70 dark:bg-neutral-900/70 backdrop-blur-md -z-10"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <x-app-logo size="sm" />

        <div class="flex items-center gap-3">
            @include('livewire.wishlist-manager.account-menu')
        </div>
    </div>
</header>
