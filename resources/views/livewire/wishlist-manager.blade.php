<div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] flex flex-col justify-between">
    <div>
        @include('livewire.wishlist-manager.navigation')

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
            @include('livewire.wishlist-manager.header')

            @include('livewire.wishlist-manager.wish-list')
        </main>
    </div>

    @include('livewire.wishlist-manager.wish-form-modal')

    @include('livewire.wishlist-manager.delete-wish-modal')

    @include('livewire.wishlist-manager.profile-modal')
</div>
