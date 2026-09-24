<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
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
            ->with('user')
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

    /**
     * Determine if the authenticated user is friends with the wishlist owner.
     */
    #[Computed]
    public function isFriend(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        return $user->isFriendWith($this->wishlist->user);
    }

    /**
     * Determine if the authenticated user is the owner of the wishlist.
     */
    #[Computed]
    public function isOwner(): bool
    {
        return Auth::check() && Auth::id() === $this->wishlist->user_id;
    }

    /**
     * Get the authenticated user's friends with their wishlists.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function friends(): Collection
    {
        if (! Auth::check()) {
            return new Collection;
        }

        /** @var User $user */
        $user = Auth::user();

        return $user->friends()
            ->with(['wishlists' => fn ($query) => $query->latest('id')->withCount('wishes')])
            ->orderBy('name')
            ->get();
    }

    /**
     * Add the wishlist owner as a friend.
     */
    public function addFriend(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));

            return;
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->id !== $this->wishlist->user_id) {
            $user->addFriend($this->wishlist->user);
            unset($this->isFriend);
            unset($this->friends);
        }
    }

    /**
     * Remove a friend from the authenticated user's friends list.
     */
    public function removeFriend(?int $friendId = null): void
    {
        if (! Auth::check()) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();

        if ($friendId !== null) {
            $user->friends()->detach($friendId);
        } else {
            $user->removeFriend($this->wishlist->user);
        }

        unset($this->isFriend);
        unset($this->friends);
    }

    public function render(): View
    {
        $titleParts = array_filter([
            $this->wishlist->title,
            $this->wishlist->user?->name,
            'Gavelisten',
        ]);

        return view('livewire.public-wishlist')
            ->title(implode(' / ', $titleParts));
    }
}
