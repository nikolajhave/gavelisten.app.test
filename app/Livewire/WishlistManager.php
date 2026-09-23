<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Wishlist Manager')]
class WishlistManager extends Component
{
    #[Validate(['required', 'string', 'max:255'], as: 'title')]
    public string $title = '';

    #[Validate(['nullable', 'string', 'max:5000'], as: 'description')]
    public ?string $description = null;

    #[Validate(['nullable', 'url', 'max:2048'], as: 'link')]
    public ?string $url = null;

    #[Validate(['nullable', 'numeric', 'min:0', 'max:9999999.99'], as: 'price')]
    public ?string $price = null;

    public string $wishlistTitle = '';

    public bool $isEditingWishlistTitle = false;

    public bool $showFormModal = false;

    public ?int $editingWishId = null;

    public bool $showDeleteModal = false;

    public ?int $deletingWishId = null;

    public ?string $deletingWishTitle = null;

    /**
     * Get the authenticated user's active wishlist.
     */
    #[Computed]
    public function wishlist(): Wishlist
    {
        /** @var User $user */
        $user = Auth::user();

        $wishlist = $user->wishlists()->first();

        if (! $wishlist) {
            $wishlist = $user->wishlists()->create([
                'title' => __('My Wishlist'),
            ]);
        }

        return $wishlist;
    }

    /**
     * Get the wishes for the wishlist ordered by sort_order.
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

    /**
     * Get the authenticated user's friends with their wishlists.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function friends(): Collection
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->friends()
            ->with(['wishlists' => fn ($query) => $query->latest('id')->withCount('wishes')])
            ->orderBy('name')
            ->get();
    }

    /**
     * Remove a friend from the authenticated user's friends list.
     */
    public function removeFriend(int $friendId): void
    {
        /** @var User $user */
        $user = Auth::user();

        $user->friends()->detach($friendId);
        unset($this->friends);
    }

    /**
     * Start editing the wishlist title.
     */
    public function startEditingWishlistTitle(): void
    {
        $this->wishlistTitle = $this->wishlist->title;
        $this->isEditingWishlistTitle = true;
        $this->resetErrorBag('wishlistTitle');
    }

    /**
     * Cancel editing the wishlist title.
     */
    public function cancelEditingWishlistTitle(): void
    {
        $this->isEditingWishlistTitle = false;
        $this->wishlistTitle = '';
        $this->resetErrorBag('wishlistTitle');
    }

    /**
     * Save the updated wishlist title.
     */
    public function saveWishlistTitle(): void
    {
        $this->validate([
            'wishlistTitle' => ['required', 'string', 'max:255'],
        ], [], [
            'wishlistTitle' => __('Wishlist title'),
        ]);

        $this->wishlist->update([
            'title' => trim($this->wishlistTitle),
        ]);

        unset($this->wishlist);

        $this->isEditingWishlistTitle = false;
    }

    /**
     * Open modal to create a new wish.
     */
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingWishId = null;
        $this->showFormModal = true;
    }

    /**
     * Open modal to edit an existing wish.
     */
    public function openEditModal(int $wishId): void
    {
        $this->resetForm();

        $wish = $this->wishlist->wishes()->findOrFail($wishId);

        $this->editingWishId = $wish->id;
        $this->title = $wish->title;
        $this->description = $wish->description;
        $this->url = $wish->url;
        $this->price = $wish->price !== null ? str_replace('.', ',', (string) $wish->price) : null;

        $this->showFormModal = true;
    }

    /**
     * Close the create/edit modal and reset form state.
     */
    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    /**
     * Save the newly created or edited wish.
     */
    public function saveWish(): void
    {
        if ($this->price !== null && trim((string) $this->price) !== '') {
            $this->price = str_replace(',', '.', trim((string) $this->price));
        }

        $this->validate();

        $formattedPrice = null;
        if ($this->price !== null && trim((string) $this->price) !== '') {
            $formattedPrice = (float) $this->price;
        }

        $data = [
            'title' => trim($this->title),
            'description' => filled($this->description) ? trim($this->description) : null,
            'url' => filled($this->url) ? trim($this->url) : null,
            'price' => $formattedPrice,
        ];

        if ($this->editingWishId) {
            $wish = $this->wishlist->wishes()->findOrFail($this->editingWishId);
            $wish->update($data);
        } else {
            $maxSortOrder = (int) $this->wishlist->wishes()->max('sort_order');
            $this->wishlist->wishes()->create([
                ...$data,
                'sort_order' => $maxSortOrder + 1,
            ]);
        }

        unset($this->wishes);
        $this->closeFormModal();
    }

    /**
     * Reorder wishes in the wishlist using Livewire 4's wire:sort.
     */
    public function reorderWishes(mixed $order, ?int $position = null): void
    {
        $wishlist = $this->wishlist;

        if (is_array($order)) {
            $index = 0;
            foreach ($order as $key => $value) {
                $wishId = is_array($value)
                    ? ($value['value'] ?? $value['id'] ?? $key)
                    : (is_numeric($value) ? (int) $value : (int) $key);

                $wishlist->wishes()->where('id', $wishId)->update(['sort_order' => $index]);
                $index++;
            }
        } elseif (is_numeric($order) && $position !== null) {
            $wishes = $wishlist->wishes()->orderBy('sort_order')->orderBy('id')->get();
            $targetWish = $wishes->firstWhere('id', (int) $order);

            if ($targetWish) {
                $remaining = $wishes->reject(fn (Wish $w) => $w->id === (int) $order)->values();
                $remaining->splice($position, 0, [$targetWish]);

                foreach ($remaining as $idx => $w) {
                    $w->update(['sort_order' => $idx]);
                }
            }
        }

        unset($this->wishes);
    }

    /**
     * Prompt delete confirmation modal for a wish.
     */
    public function confirmDeleteWish(int $wishId): void
    {
        $wish = $this->wishlist->wishes()->findOrFail($wishId);
        $this->deletingWishId = $wish->id;
        $this->deletingWishTitle = $wish->title;
        $this->showDeleteModal = true;
    }

    /**
     * Close the delete confirmation modal.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingWishId = null;
        $this->deletingWishTitle = null;
    }

    /**
     * Delete the confirmed wish.
     */
    public function deleteWish(): void
    {
        if ($this->deletingWishId) {
            $this->wishlist->wishes()->where('id', $this->deletingWishId)->delete();
            unset($this->wishes);
        }

        $this->closeDeleteModal();
    }

    /**
     * Reset form input fields and error messages.
     */
    protected function resetForm(): void
    {
        $this->title = '';
        $this->description = null;
        $this->url = null;
        $this->price = null;
        $this->editingWishId = null;
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.wishlist-manager');
    }
}
