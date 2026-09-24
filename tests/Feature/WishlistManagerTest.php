<?php

use App\Livewire\WishlistManager;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('unauthenticated users are redirected to login', function () {
    $response = $this->get('/wishlist');

    $response->assertRedirect('/login');
});

test('authenticated user can view their wishlist manager', function () {
    $user = User::factory()->create([
        'name' => 'Nikolaj',
    ]);
    $wishlist = $user->wishlists()->first();
    $wishlist->update(['title' => 'Min Fødselsdagsliste']);

    $response = $this->actingAs($user)->get('/wishlist');

    $response->assertStatus(200);
    $response->assertSee('<title>Min Fødselsdagsliste / Nikolaj / Gavelisten</title>', false);
    $response->assertSee('Nikolaj');
    $response->assertSee('Min Fødselsdagsliste');
    $response->assertSee(__('Add Wish'));
});

test('wishes are rendered in ascending sort_order', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $wishThird = Wish::factory()->for($wishlist)->create([
        'title' => 'Third Item',
        'sort_order' => 2,
    ]);
    $wishFirst = Wish::factory()->for($wishlist)->create([
        'title' => 'First Item',
        'sort_order' => 0,
    ]);
    $wishSecond = Wish::factory()->for($wishlist)->create([
        'title' => 'Second Item',
        'sort_order' => 1,
    ]);

    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->assertSeeInOrder(['First Item', 'Second Item', 'Third Item']);
});

test('user can create a new wish', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openCreateModal')
        ->assertSet('showFormModal', true)
        ->set('title', 'Nintendo Switch 2')
        ->set('description', 'With Mario Kart bundle')
        ->set('url', 'https://example.com/switch2')
        ->set('price', '3499.00')
        ->call('saveWish')
        ->assertSet('showFormModal', false);

    $wishlist = $user->wishlists()->first();

    expect($wishlist->wishes()->count())->toBe(1);

    $this->assertDatabaseHas('wishes', [
        'wishlist_id' => $wishlist->id,
        'title' => 'Nintendo Switch 2',
        'description' => 'With Mario Kart bundle',
        'url' => 'https://example.com/switch2',
        'price' => 3499.00,
        'sort_order' => 1,
    ]);
});

test('validates required and format rules on wish fields', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->set('title', '')
        ->set('price', 'not-a-number')
        ->set('url', 'invalid-url')
        ->call('saveWish')
        ->assertHasErrors([
            'title' => 'required',
            'price' => 'numeric',
            'url' => 'url',
        ]);
});

test('user can edit an existing wish', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $wish = Wish::factory()->for($wishlist)->create([
        'title' => 'Original Title',
        'description' => 'Old description',
        'price' => 100.00,
        'url' => 'https://example.com/old',
    ]);

    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openEditModal', $wish->id)
        ->assertSet('showFormModal', true)
        ->assertSet('editingWishId', $wish->id)
        ->assertSet('title', 'Original Title')
        ->assertSet('description', 'Old description')
        ->assertSet('price', '100,00')
        ->assertSet('url', 'https://example.com/old')
        ->set('title', 'Brand New Title')
        ->set('price', '149,95')
        ->call('saveWish')
        ->assertSet('showFormModal', false);

    $this->assertDatabaseHas('wishes', [
        'id' => $wish->id,
        'title' => 'Brand New Title',
        'price' => 149.95,
    ]);
});

test('user cannot edit a wish belonging to another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user2Wishlist = $user2->wishlists()->first();
    $otherWish = Wish::factory()->for($user2Wishlist)->create(['title' => 'Secret Gift']);

    $this->actingAs($user1);

    Livewire::test(WishlistManager::class)
        ->call('openEditModal', $otherWish->id)
        ->assertStatus(404);
});

test('user can delete a wish after confirmation', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $wish = Wish::factory()->for($wishlist)->create([
        'title' => 'Item to delete',
    ]);

    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('confirmDeleteWish', $wish->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deletingWishId', $wish->id)
        ->assertSet('deletingWishTitle', 'Item to delete')
        ->call('deleteWish')
        ->assertSet('showDeleteModal', false);

    $this->assertDatabaseMissing('wishes', [
        'id' => $wish->id,
    ]);
});

test('user can reorder wishes using wire:sort array format', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $wishA = Wish::factory()->for($wishlist)->create(['title' => 'A', 'sort_order' => 0]);
    $wishB = Wish::factory()->for($wishlist)->create(['title' => 'B', 'sort_order' => 1]);
    $wishC = Wish::factory()->for($wishlist)->create(['title' => 'C', 'sort_order' => 2]);

    $this->actingAs($user);

    // Reorder as C, A, B
    Livewire::test(WishlistManager::class)
        ->call('reorderWishes', [$wishC->id, $wishA->id, $wishB->id]);

    expect($wishC->fresh()->sort_order)->toBe(0)
        ->and($wishA->fresh()->sort_order)->toBe(1)
        ->and($wishB->fresh()->sort_order)->toBe(2);
});

test('user can reorder wishes using item and new position arguments', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $wishA = Wish::factory()->for($wishlist)->create(['title' => 'A', 'sort_order' => 0]);
    $wishB = Wish::factory()->for($wishlist)->create(['title' => 'B', 'sort_order' => 1]);
    $wishC = Wish::factory()->for($wishlist)->create(['title' => 'C', 'sort_order' => 2]);

    $this->actingAs($user);

    // Move C to position 0: C (0), A (1), B (2)
    Livewire::test(WishlistManager::class)
        ->call('reorderWishes', $wishC->id, 0);

    expect($wishC->fresh()->sort_order)->toBe(0)
        ->and($wishA->fresh()->sort_order)->toBe(1)
        ->and($wishB->fresh()->sort_order)->toBe(2);
});

test('user cannot delete a wish belonging to another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user2Wishlist = $user2->wishlists()->first();
    $otherWish = Wish::factory()->for($user2Wishlist)->create(['title' => 'Secret Gift']);

    $this->actingAs($user1);

    Livewire::test(WishlistManager::class)
        ->call('confirmDeleteWish', $otherWish->id)
        ->assertStatus(404);

    $this->assertDatabaseHas('wishes', [
        'id' => $otherWish->id,
    ]);
});

test('handles price formatting with commas and empty optional fields', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openCreateModal')
        ->set('title', 'Simple Wish')
        ->set('price', '249,50')
        ->set('url', '')
        ->set('description', '')
        ->call('saveWish')
        ->assertSet('showFormModal', false);

    $this->assertDatabaseHas('wishes', [
        'title' => 'Simple Wish',
        'price' => 249.50,
        'url' => null,
        'description' => null,
    ]);
});

test('closing modal resets form values and error bag', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->set('title', 'Some Title')
        ->set('price', 'invalid')
        ->set('showFormModal', true)
        ->call('closeFormModal')
        ->assertSet('showFormModal', false)
        ->assertSet('title', '')
        ->assertSet('price', null)
        ->assertHasNoErrors();
});

test('user can start editing, cancel editing, and save updated wishlist title', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->assertSet('isEditingWishlistTitle', false)
        ->call('startEditingWishlistTitle')
        ->assertSet('isEditingWishlistTitle', true)
        ->assertSet('wishlistTitle', $wishlist->title)
        ->set('wishlistTitle', 'Temporary Title')
        ->call('cancelEditingWishlistTitle')
        ->assertSet('isEditingWishlistTitle', false)
        ->assertSet('wishlistTitle', '')
        ->call('startEditingWishlistTitle')
        ->set('wishlistTitle', 'Nikolajs Fødselsdag 2026')
        ->call('saveWishlistTitle')
        ->assertSet('isEditingWishlistTitle', false)
        ->assertSee('Nikolajs Fødselsdag 2026');

    expect($wishlist->fresh()->title)->toBe('Nikolajs Fødselsdag 2026');
});

test('validates wishlist title rules when updating wishlist title', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('startEditingWishlistTitle')
        ->set('wishlistTitle', '')
        ->call('saveWishlistTitle')
        ->assertHasErrors(['wishlistTitle' => 'required']);
});

test('wish title button triggers openEditModal', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $wish = Wish::factory()->for($wishlist)->create([
        'title' => 'Bose QuietComfort Ultra',
        'price' => 2799.00,
    ]);

    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openEditModal', $wish->id)
        ->assertSet('showFormModal', true)
        ->assertSet('editingWishId', $wish->id)
        ->assertSet('title', 'Bose QuietComfort Ultra')
        ->assertSet('price', '2799,00');
});

test('editing a wish formats price with comma decimal separator', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $wish = Wish::factory()->for($wishlist)->create([
        'title' => 'Special Wish',
        'price' => 123.99,
    ]);

    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openEditModal', $wish->id)
        ->assertSet('price', '123,99')
        ->set('price', '124,50')
        ->call('saveWish');

    expect($wish->fresh()->price)->toBe('124.50');
});

test('wishlist manager renders share dropdown button and responsive actions', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    $this->actingAs($user);

    $component = Livewire::test(WishlistManager::class);

    $component->assertSee(__('Share Wishlist'))
        ->assertSee(__('Copy Link'))
        ->assertSee(__('See Public Wishlist'))
        ->assertSee($wishlist->share_token)
        ->assertSee(__('Friends'))
        ->assertSee(__('Sign out'));
});

test('renders empty state with distinct wire:key when wishlist has no wishes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->assertSeeHtml('wire:key="wishlist-empty-state"')
        ->assertDontSeeHtml('wire:key="wishlist-items-list"')
        ->assertSee(__('No wishes yet'));
});

test('transitions from empty state to sortable list on first run and allows immediate reordering', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(WishlistManager::class)
        ->assertSeeHtml('wire:key="wishlist-empty-state"')
        ->assertDontSeeHtml('wire:key="wishlist-items-list"')
        ->call('openCreateModal')
        ->set('title', 'First Wish')
        ->call('saveWish')
        ->assertSeeHtml('wire:key="wishlist-items-list"')
        ->assertSeeHtml('wire:sort="reorderWishes"')
        ->call('openCreateModal')
        ->set('title', 'Second Wish')
        ->call('saveWish');

    $wishlist = $user->wishlists()->first();
    $wishes = $wishlist->wishes()->orderBy('sort_order')->get();

    expect($wishes)->toHaveCount(2);

    $firstWish = $wishes->firstWhere('title', 'First Wish');
    $secondWish = $wishes->firstWhere('title', 'Second Wish');

    // Reorder immediately without page reload
    $component->call('reorderWishes', [$secondWish->id, $firstWish->id]);

    expect($secondWish->fresh()->sort_order)->toBe(0)
        ->and($firstWish->fresh()->sort_order)->toBe(1);
});

test('transitions back to empty state after deleting all wishes and can create sortable wishes again', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(WishlistManager::class)
        ->call('openCreateModal')
        ->set('title', 'Temporary Wish')
        ->call('saveWish')
        ->assertSeeHtml('wire:key="wishlist-items-list"');

    $wish = $user->wishlists()->first()->wishes()->first();

    $component->call('confirmDeleteWish', $wish->id)
        ->call('deleteWish')
        ->assertSeeHtml('wire:key="wishlist-empty-state"')
        ->assertDontSeeHtml('wire:key="wishlist-items-list"');

    // Create again and check transition back to sortable list
    $component->call('openCreateModal')
        ->set('title', 'New Wish After Delete')
        ->call('saveWish')
        ->assertSeeHtml('wire:key="wishlist-items-list"')
        ->assertSeeHtml('wire:sort="reorderWishes"');
});
