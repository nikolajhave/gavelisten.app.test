<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistUser;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WishlistRelations extends Component
{
    /**
     * Search query for filtering relations.
     */
    public string $search = '';

    /**
     * Filter by relation type ('all', 'delegated', 'owned').
     */
    public string $typeFilter = 'all';

    /**
     * Sort column ('created_at', 'user', 'wishlist', 'id').
     */
    public string $sortBy = 'created_at';

    /**
     * Sort direction ('asc', 'desc').
     */
    public string $sortDir = 'desc';

    /**
     * Initialize component and ensure admin authorization.
     */
    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            abort(403);
        }
    }

    /**
     * Clear search filter.
     */
    public function clearSearch(): void
    {
        $this->search = '';
    }

    /**
     * Change active tab filter.
     */
    public function setTab(string $tab): void
    {
        if (in_array($tab, ['all', 'delegated', 'owned'], true)) {
            $this->typeFilter = $tab;
        }
    }

    /**
     * Toggle sorting direction or set sort column.
     */
    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'desc';
        }
    }

    /**
     * Get system statistics.
     *
     * @return array{total_relations: int, delegated_count: int, owned_count: int, users_count: int, admins_count: int}
     */
    #[Computed]
    public function stats(): array
    {
        $delegatedCount = WishlistUser::count();
        $ownedCount = Wishlist::count();
        $usersCount = User::count();
        $adminsCount = User::where('is_admin', true)->count();

        return [
            'total_relations' => $delegatedCount + $ownedCount,
            'delegated_count' => $delegatedCount,
            'owned_count' => $ownedCount,
            'users_count' => $usersCount,
            'admins_count' => $adminsCount,
        ];
    }

    /**
     * Get all relations formatted for tabular display.
     *
     * @return Collection<int, array<string, mixed>>
     */
    #[Computed]
    public function relations(): Collection
    {
        $search = trim($this->search);
        $items = collect();

        // Include Delegated relations (from wishlist_user)
        if ($this->typeFilter === 'all' || $this->typeFilter === 'delegated') {
            $delegatedQuery = WishlistUser::query()
                ->with([
                    'user',
                    'wishlist' => fn ($query) => $query->with('user')->withCount('wishes'),
                ]);

            if ($search !== '') {
                $delegatedQuery->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->whereLike('name', "%{$search}%", caseSensitive: false)
                            ->orWhereLike('email', "%{$search}%", caseSensitive: false)
                            ->orWhereLike('phone', "%{$search}%");
                    })->orWhereHas('wishlist', function ($q) use ($search) {
                        $q->whereLike('title', "%{$search}%", caseSensitive: false)
                            ->orWhereHas('user', function ($ownerQuery) use ($search) {
                                $ownerQuery->whereLike('name', "%{$search}%", caseSensitive: false)
                                    ->orWhereLike('email', "%{$search}%", caseSensitive: false);
                            });
                    });
                });
            }

            $delegatedRows = $delegatedQuery->get();

            foreach ($delegatedRows as $row) {
                $items->push([
                    'key' => 'delegated-'.$row->id,
                    'pivot_id' => $row->id,
                    'type' => 'delegated',
                    'type_label' => 'Delegated Editor',
                    'user_id' => $row->user_id,
                    'user_name' => $row->user?->name ?? __('Unknown user'),
                    'user_email' => $row->user?->email,
                    'user_phone' => $row->user?->phone,
                    'user_is_admin' => (bool) $row->user?->is_admin,
                    'wishlist_id' => $row->wishlist_id,
                    'wishlist_title' => $row->wishlist?->title ?? __('Unknown wishlist'),
                    'wishlist_share_token' => $row->wishlist?->share_token,
                    'wishes_count' => $row->wishlist?->wishes_count ?? 0,
                    'owner_id' => $row->wishlist?->user_id,
                    'owner_name' => $row->wishlist?->user?->name ?? __('Unknown owner'),
                    'owner_email' => $row->wishlist?->user?->email,
                    'created_at' => $row->created_at,
                ]);
            }
        }

        // Include Owned wishlists (wishlists.user_id)
        if ($this->typeFilter === 'all' || $this->typeFilter === 'owned') {
            $ownedQuery = Wishlist::query()
                ->with(['user', 'delegatedUsers'])
                ->withCount('wishes');

            if ($search !== '') {
                $ownedQuery->where(function ($query) use ($search) {
                    $query->whereLike('title', "%{$search}%", caseSensitive: false)
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->whereLike('name', "%{$search}%", caseSensitive: false)
                                ->orWhereLike('email', "%{$search}%", caseSensitive: false)
                                ->orWhereLike('phone', "%{$search}%");
                        });
                });
            }

            $ownedRows = $ownedQuery->get();

            foreach ($ownedRows as $wishlist) {
                $items->push([
                    'key' => 'owned-'.$wishlist->id,
                    'pivot_id' => null,
                    'type' => 'owned',
                    'type_label' => 'Owner',
                    'user_id' => $wishlist->user_id,
                    'user_name' => $wishlist->user?->name ?? __('Unknown owner'),
                    'user_email' => $wishlist->user?->email,
                    'user_phone' => $wishlist->user?->phone,
                    'user_is_admin' => (bool) $wishlist->user?->is_admin,
                    'wishlist_id' => $wishlist->id,
                    'wishlist_title' => $wishlist->title,
                    'wishlist_share_token' => $wishlist->share_token,
                    'wishes_count' => $wishlist->wishes_count ?? 0,
                    'owner_id' => $wishlist->user_id,
                    'owner_name' => $wishlist->user?->name ?? __('Unknown owner'),
                    'owner_email' => $wishlist->user?->email,
                    'delegated_count' => $wishlist->delegatedUsers->count(),
                    'created_at' => $wishlist->created_at,
                ]);
            }
        }

        // Sort items
        return $items->sort(function (array $a, array $b): int {
            $direction = $this->sortDir === 'asc' ? 1 : -1;

            if ($this->sortBy === 'user') {
                return $direction * strnatcasecmp($a['user_name'], $b['user_name']);
            }

            if ($this->sortBy === 'wishlist') {
                return $direction * strnatcasecmp($a['wishlist_title'], $b['wishlist_title']);
            }

            if ($this->sortBy === 'id') {
                $idA = $a['pivot_id'] ?? $a['wishlist_id'];
                $idB = $b['pivot_id'] ?? $b['wishlist_id'];

                return $direction * ($idA <=> $idB);
            }

            // Default: created_at
            $timeA = $a['created_at']?->timestamp ?? 0;
            $timeB = $b['created_at']?->timestamp ?? 0;

            return $direction * ($timeA <=> $timeB);
        })->values();
    }

    public function render(): View
    {
        return view('livewire.admin.wishlist-relations')
            ->title(__('Admin - Wishlist Relations / Gavelisten'));
    }
}
