### Updated Specification: Delegated Wishlist Access (Parent → Child)

This revised specification focuses strictly on **delegated list access** with **no multi-list creation UI** and **no member-management UI**.

The delegation table will be populated directly in the database (manually or via legacy data import scripts), and the UI will simply allow users who have delegated lists to switch to and manage them.

---

### 1. Architectural Summary & Scope

- **Single List Model Kept**: Each user still owns their one primary wishlist.
- **Delegation via Pivot Table**: A user (e.g. Parent) is linked to another user's wishlist (e.g. Child) in a pivot table (`wishlist_user`).
- **No Management UI**: No buttons or modals to create new wishlists or invite collaborators.
- **Conditional List Switching**: If a user has delegated lists in the database, a simple list selector appears in the UI. If they have no delegated lists, the UI behaves exactly as it does today.

---

### 2. Database Schema: `wishlist_user`

Create a pivot table linking `wishlist_id` and `user_id`.

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['wishlist_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_user');
    }
};
```

#### How Data is Seeded / Migrated Manually
To give Parent (`user_id = 1`) edit access to Child's wishlist (`wishlist_id = 42`):
```sql
INSERT INTO wishlist_user (wishlist_id, user_id, created_at, updated_at)
VALUES (42, 1, NOW(), NOW());
```
Or in a legacy migration / seeder:
```php
DB::table('wishlist_user')->insertOrIgnore([
    'wishlist_id' => $childWishlist->id,
    'user_id' => $parentUser->id,
    'created_at' => now(),
    'updated_at' => now(),
]);
```

---

### 3. Eloquent Models & Relationships

#### `App\Models\Wishlist.php`
```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wishlist extends Model
{
    // The user who owns this wishlist (e.g. the child)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Users who have delegated admin/edit access (e.g. parents)
    public function delegatedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlist_user')
            ->withTimestamps();
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(Wish::class);
    }

    /**
     * Check if a given user can edit this wishlist (either owner or delegate).
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->delegatedUsers()->where('users.id', $user->id)->exists();
    }
}
```

#### `App\Models\User.php`
```php
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    // The user's own wishlist
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // Wishlists of other users that this user can edit
    public function delegatedWishlists(): BelongsToMany
    {
        return $this->belongsToMany(Wishlist::class, 'wishlist_user')
            ->withTimestamps();
    }

    /**
     * Get all wishlists this user can edit (own wishlist + delegated).
     */
    public function allEditableWishlists()
    {
        return Wishlist::query()
            ->with('user')
            ->where('user_id', $this->id)
            ->orWhereHas('delegatedUsers', function ($query) {
                $query->where('users.id', $this->id);
            });
    }
}
```

---

### 4. Authorization & Policies

#### `App\Policies\WishlistPolicy.php`
```php
namespace App\Policies;

use App\Models\User;
use App\Models\Wishlist;

class WishlistPolicy
{
    public function view(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->canBeEditedBy($user);
    }

    public function update(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->canBeEditedBy($user);
    }

    public function addWish(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->canBeEditedBy($user);
    }
}
```

#### `App\Policies\WishPolicy.php`
```php
namespace App\Policies;

use App\Models\User;
use App\Models\Wish;

class WishPolicy
{
    public function update(User $user, Wish $wish): bool
    {
        return $wish->wishlist->canBeEditedBy($user);
    }

    public function delete(User $user, Wish $wish): bool
    {
        return $wish->wishlist->canBeEditedBy($user);
    }
}
```

---

### 5. `WishlistManager` (Livewire) Integration

Since we don't have a "Create Wishlist" flow, `WishlistManager` only needs to:
1. Identify all wishlists accessible to the logged-in user (`$user->allEditableWishlists()`).
2. Track the currently active wishlist ID (defaulting to the user's own wishlist).
3. If more than 1 wishlist is available (i.e. user has delegated lists), render a switcher inside the account menu modal under the profile.

#### Component Logic:
```php
class WishlistManager extends Component
{
    public ?int $selectedWishlistId = null;

    /**
     * Get all wishlists the user can edit (own + delegated).
     *
     * @return Collection<int, Wishlist>
     */
    #[Computed]
    public function availableWishlists(): Collection
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->allEditableWishlists()->get();
    }

    /**
     * Active wishlist currently being viewed/edited.
     */
    #[Computed]
    public function wishlist(): Wishlist
    {
        /** @var User $user */
        $user = Auth::user();

        if ($this->selectedWishlistId) {
            $selected = $this->availableWishlists->firstWhere('id', $this->selectedWishlistId);
            if ($selected) {
                return $selected;
            }
        }

        // Default to user's own list
        $ownList = $user->wishlists()->first();
        if (! $ownList) {
            $ownList = $user->wishlists()->create(['title' => __('My Wishlist')]);
        }

        $this->selectedWishlistId = $ownList->id;

        return $ownList;
    }

    public function selectWishlist(int $wishlistId): void
    {
        $this->selectedWishlistId = $wishlistId;
        unset($this->wishlist, $this->wishes);
    }
}
```

#### UI: Switcher in Account Menu Modal under Profile

In `resources/views/livewire/wishlist-manager/account-menu.blade.php`, render the switcher under the profile section:

```blade
<x-wishlist.account-menu :user="auth()->user()">
    <x-slot:profileAction>
        <button
            type="button"
            @click="open = false; $wire.showProfileModal = true"
            wire:click="openProfileModal"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition cursor-pointer shrink-0"
            title="{{ __('Edit Profile') }}"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <span>{{ __('Profile') }}</span>
        </button>
    </x-slot:profileAction>

    {{-- Switcher: Placed under the Profile in the Menu Modal --}}
    @if($this->availableWishlists->count() > 1)
        <div class="px-4 py-2.5 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-900/50">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500 mb-1.5">
                {{ __('Switch Wishlist') }}
            </p>
            <div class="space-y-1">
                @foreach($this->availableWishlists as $list)
                    @php
                        $isActive = ($list->id === $this->wishlist->id);
                        $isOwn = ($list->user_id === auth()->id());
                    @endphp
                    <button
                        type="button"
                        wire:click="selectWishlist({{ $list->id }})"
                        @click="open = false"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition cursor-pointer {{ $isActive ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-semibold' : 'text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800' }}"
                    >
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="truncate">
                                {{ $list->title }}
                                @if(! $isOwn)
                                    <span class="text-[11px] opacity-75">({{ $list->user->name }})</span>
                                @endif
                            </span>
                        </div>
                        @if($isActive)
                            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @include('livewire.wishlist-manager.friends-panel')

    <x-slot:footer>
        ...
    </x-slot:footer>
</x-wishlist.account-menu>
```

---

### 6. Summary of What to Implement When Ready

1. **Migration**: Create `wishlist_user` table.
2. **Relationships**: Add `delegatedUsers()` to `Wishlist` and `delegatedWishlists()` / `allEditableWishlists()` to `User`.
3. **Policies**: Add `canBeEditedBy()` checks in `WishlistPolicy` and `WishPolicy`.
4. **Livewire**: Add `selectedWishlistId`, `availableWishlists()`, and `selectWishlist(int $wishlistId)` to `WishlistManager`.
5. **Blade View**: Add the switcher into `resources/views/livewire/wishlist-manager/account-menu.blade.php` under the profile section.
6. **Populate Data**: Insert legacy relation pairs into `wishlist_user` directly or via legacy import.
