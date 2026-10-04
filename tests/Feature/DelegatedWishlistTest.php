<?php

use App\Livewire\WishlistManager;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('user without delegated wishlists does not see switcher in account menu', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->assertDontSee(__('Switch Wishlist'))
        ->assertDontSee(__('Wishlists'));
});

test('parent user with delegated wishlist sees switcher in account menu', function () {
    $parent = User::factory()->create(['name' => 'John Parent']);
    $child = User::factory()->create(['name' => 'Emma Child']);

    $parentWishlist = $parent->wishlists()->first();
    $childWishlist = $child->wishlists()->first();
    $childWishlist->update(['title' => 'Emma 5th Birthday']);

    $childWishlist->delegatedUsers()->attach($parent->id);

    $this->actingAs($parent);

    Livewire::test(WishlistManager::class)
        ->assertSee(__('Wishlists'))
        ->assertSee('John Parent')
        ->assertSee('Emma Child')
        ->assertSee('(Emma 5th Birthday)');
});

test('parent can select child wishlist and view its wishes', function () {
    $parent = User::factory()->create(['name' => 'John Parent']);
    $child = User::factory()->create(['name' => 'Emma Child']);

    $parentWishlist = $parent->wishlists()->first();
    $childWishlist = $child->wishlists()->first();
    $childWishlist->update(['title' => 'Emma Wishlist']);

    Wish::factory()->for($parentWishlist)->create(['title' => 'Parent Coffee Maker']);
    Wish::factory()->for($childWishlist)->create(['title' => 'Child Lego Castle']);

    $childWishlist->delegatedUsers()->attach($parent->id);

    $this->actingAs($parent);

    $component = Livewire::test(WishlistManager::class)
        ->assertSee('Parent Coffee Maker')
        ->assertDontSee('Child Lego Castle');

    $component->call('selectWishlist', $childWishlist->id)
        ->assertSet('selectedWishlistId', $childWishlist->id)
        ->assertSee('Child Lego Castle')
        ->assertDontSee('Parent Coffee Maker');
});

test('parent can add a wish to child delegated wishlist', function () {
    $parent = User::factory()->create(['name' => 'John Parent']);
    $child = User::factory()->create(['name' => 'Emma Child']);

    $childWishlist = $child->wishlists()->first();
    $childWishlist->delegatedUsers()->attach($parent->id);

    $this->actingAs($parent);

    Livewire::test(WishlistManager::class)
        ->call('selectWishlist', $childWishlist->id)
        ->call('openCreateModal')
        ->set('title', 'Paw Patrol Toy')
        ->set('price', '199.95')
        ->call('saveWish')
        ->assertSet('showFormModal', false);

    $this->assertDatabaseHas('wishes', [
        'wishlist_id' => $childWishlist->id,
        'title' => 'Paw Patrol Toy',
        'price' => 199.95,
    ]);
});

test('parent can edit a wish on child delegated wishlist', function () {
    $parent = User::factory()->create(['name' => 'John Parent']);
    $child = User::factory()->create(['name' => 'Emma Child']);

    $childWishlist = $child->wishlists()->first();
    $childWishlist->delegatedUsers()->attach($parent->id);

    $wish = Wish::factory()->for($childWishlist)->create([
        'title' => 'Teddy Bear',
        'price' => 100.00,
    ]);

    $this->actingAs($parent);

    Livewire::test(WishlistManager::class)
        ->call('selectWishlist', $childWishlist->id)
        ->call('openEditModal', $wish->id)
        ->set('title', 'Giant Teddy Bear')
        ->set('price', '250.00')
        ->call('saveWish')
        ->assertSet('showFormModal', false);

    expect($wish->fresh()->title)->toBe('Giant Teddy Bear')
        ->and((float) $wish->fresh()->price)->toBe(250.00);
});

test('parent can delete a wish on child delegated wishlist', function () {
    $parent = User::factory()->create(['name' => 'John Parent']);
    $child = User::factory()->create(['name' => 'Emma Child']);

    $childWishlist = $child->wishlists()->first();
    $childWishlist->delegatedUsers()->attach($parent->id);

    $wish = Wish::factory()->for($childWishlist)->create(['title' => 'Duplicate Toy']);

    $this->actingAs($parent);

    Livewire::test(WishlistManager::class)
        ->call('selectWishlist', $childWishlist->id)
        ->call('confirmDeleteWish', $wish->id)
        ->assertSet('showDeleteModal', true)
        ->call('deleteWish')
        ->assertSet('showDeleteModal', false);

    $this->assertDatabaseMissing('wishes', ['id' => $wish->id]);
});

test('parent can reorder wishes on child delegated wishlist', function () {
    $parent = User::factory()->create(['name' => 'John Parent']);
    $child = User::factory()->create(['name' => 'Emma Child']);

    $childWishlist = $child->wishlists()->first();
    $childWishlist->delegatedUsers()->attach($parent->id);

    $wish1 = Wish::factory()->for($childWishlist)->create(['title' => 'Wish 1', 'sort_order' => 0]);
    $wish2 = Wish::factory()->for($childWishlist)->create(['title' => 'Wish 2', 'sort_order' => 1]);

    $this->actingAs($parent);

    Livewire::test(WishlistManager::class)
        ->call('selectWishlist', $childWishlist->id)
        ->call('reorderWishes', [$wish2->id, $wish1->id]);

    expect($wish2->fresh()->sort_order)->toBe(0)
        ->and($wish1->fresh()->sort_order)->toBe(1);
});

test('parent can update title of child delegated wishlist', function () {
    $parent = User::factory()->create(['name' => 'John Parent']);
    $child = User::factory()->create(['name' => 'Emma Child']);

    $childWishlist = $child->wishlists()->first();
    $childWishlist->delegatedUsers()->attach($parent->id);

    $this->actingAs($parent);

    Livewire::test(WishlistManager::class)
        ->call('selectWishlist', $childWishlist->id)
        ->set('wishlistTitle', 'Emma Updated Birthday List')
        ->call('saveWishlistTitle')
        ->assertSet('isEditingWishlistTitle', false);

    expect($childWishlist->fresh()->title)->toBe('Emma Updated Birthday List');
});

test('user cannot select non-delegated wishlist of another user', function () {
    $user = User::factory()->create(['name' => 'User']);
    $stranger = User::factory()->create(['name' => 'Stranger']);

    $userWishlist = $user->wishlists()->first();
    $strangerWishlist = $stranger->wishlists()->first();

    $this->actingAs($user);

    $component = Livewire::test(WishlistManager::class)
        ->call('selectWishlist', $strangerWishlist->id);

    // selectedWishlistId should not change to stranger's wishlist
    expect($component->get('selectedWishlistId'))->toBe($userWishlist->id);
});
