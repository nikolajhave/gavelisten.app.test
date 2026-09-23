<?php

namespace App\Livewire;

use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PublicWishlist extends Component
{
    public string $share_token;

    public function mount(string $share_token): void
    {
        $this->share_token = $share_token;

        // Abort with 404 if the wishlist does not exist
        if (! $this->wishlist) {
            abort(404);
        }
    }

    /**
     * Get the public wishlist matching the share token.
     */
    #[Computed]
    public function wishlist(): Wishlist
    {
        return Wishlist::query()
            ->where('share_token', $this->share_token)
            ->firstOrFail();
    }

    /**
     * Get the wishes for the public wishlist ordered by sort_order.
     *
     * @return Collection<int, Wish>
     */
    #[Computed]
    public function wishes(): Collection
    {
        return $this->wishlist->wishes()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.public-wishlist')
            ->title($this->wishlist->title.' — Gavelisten');
    }
}
