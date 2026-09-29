<div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @include('livewire.wishlist-manager.header')

        @include('livewire.wishlist-manager.wish-list')
    </div>

    @include('livewire.wishlist-manager.wish-form-modal')

    @include('livewire.wishlist-manager.delete-wish-modal')

    @include('livewire.wishlist-manager.profile-modal')
</div>
