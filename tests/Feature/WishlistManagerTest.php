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
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/wishlist');

    $response->assertStatus(200);
    $response->assertSee(__('My Wishlist'));
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
        ->assertSet('price', '100.00')
        ->assertSet('url', 'https://example.com/old')
        ->set('title', 'Brand New Title')
        ->set('price', '149.95')
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
