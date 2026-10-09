<?php

use App\Livewire\Admin\WishlistRelations;
use App\Livewire\WishlistManager;
use App\Models\User;
use App\Models\Wish;
use App\Models\Wishlist;
use App\Models\WishlistUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('unauthenticated users are redirected from admin routes', function () {
    $this->get('/admin')->assertRedirect('/login');
    $this->get('/admin/wishlists')->assertRedirect('/login');
});

test('non-admin users receive 403 forbidden when accessing admin routes', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user);

    $this->get('/admin')->assertForbidden();
    $this->get('/admin/wishlists')->assertForbidden();
});

test('admin users can access the admin page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $this->get('/admin')->assertOk();
    $this->get('/admin/wishlists')->assertOk();
});

test('is_admin helper and cast work correctly', function () {
    $admin = User::factory()->admin()->create();
    $regularUser = User::factory()->create(['is_admin' => false]);

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->is_admin)->toBeTrue()
        ->and($regularUser->isAdmin())->toBeFalse()
        ->and($regularUser->is_admin)->toBeFalse();
});

test('admin page displays all relations including delegations from wishlist_user', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin Boss']);

    $owner = User::factory()->create(['name' => 'Alice Owner', 'email' => 'alice@example.com']);
    $delegate = User::factory()->create(['name' => 'Bob Delegate', 'email' => 'bob@example.com']);

    $ownerWishlist = $owner->wishlists()->first();
    $ownerWishlist->update(['title' => 'Alice Birthday Extravaganza']);

    // Attach Bob to Alice's wishlist via wishlist_user
    $ownerWishlist->delegatedUsers()->attach($delegate->id);
    Wish::factory()->for($ownerWishlist)->create(['title' => 'Lego Star Wars']);

    $this->actingAs($admin);

    Livewire::test(WishlistRelations::class)
        ->assertSee('Alice Birthday Extravaganza')
        ->assertSee('Alice Owner')
        ->assertSee('Bob Delegate')
        ->assertSee('bob@example.com')
        ->assertSee('Delegated Editor')
        ->assertSee('from wishlist_user')
        ->assertSee('Owner');
});

test('admin can filter relations by tab', function () {
    $admin = User::factory()->admin()->create();

    $owner = User::factory()->create(['name' => 'Charlie Owner']);
    $delegate = User::factory()->create(['name' => 'Daisy Delegate']);

    $ownerWishlist = $owner->wishlists()->first();
    $ownerWishlist->update(['title' => 'Charlie Holiday List']);
    $ownerWishlist->delegatedUsers()->attach($delegate->id);

    $this->actingAs($admin);

    // Testing 'delegated' tab
    $component = Livewire::test(WishlistRelations::class)
        ->call('setTab', 'delegated')
        ->assertSet('typeFilter', 'delegated')
        ->assertSee('Daisy Delegate')
        ->assertSee('Delegated Editor');

    expect($component->get('relations')->pluck('type')->unique()->toArray())->toBe(['delegated']);

    // Testing 'owned' tab
    $component->call('setTab', 'owned')
        ->assertSet('typeFilter', 'owned')
        ->assertSee('Charlie Holiday List')
        ->assertSee('Owner');

    expect($component->get('relations')->pluck('type')->unique()->toArray())->toBe(['owned']);
});

test('admin can search relations by user name, email, or wishlist title', function () {
    $admin = User::factory()->admin()->create();

    $userA = User::factory()->create(['name' => 'UniqueZebra', 'email' => 'zebra@savannah.org']);
    $userB = User::factory()->create(['name' => 'OrdinaryLion', 'email' => 'lion@savannah.org']);

    $wishlistA = $userA->wishlists()->first();
    $wishlistA->update(['title' => 'Zebra Wishlist Special']);

    $this->actingAs($admin);

    Livewire::test(WishlistRelations::class)
        ->set('search', 'UniqueZebra')
        ->assertSee('UniqueZebra')
        ->assertDontSee('OrdinaryLion')
        ->set('search', 'Special')
        ->assertSee('Zebra Wishlist Special')
        ->call('clearSearch')
        ->assertSet('search', '')
        ->assertSee('OrdinaryLion');
});

test('admin can sort relations by columns', function () {
    $admin = User::factory()->admin()->create();

    $userZ = User::factory()->create(['name' => 'Zack User']);
    $userA = User::factory()->create(['name' => 'Aaron User']);

    $this->actingAs($admin);

    $component = Livewire::test(WishlistRelations::class);

    $component->call('sort', 'user')
        ->assertSet('sortBy', 'user')
        ->assertSet('sortDir', 'desc');

    $firstUser = $component->get('relations')->first()['user_name'];
    expect($firstUser)->toBe('Zack User');

    $component->call('sort', 'user')
        ->assertSet('sortDir', 'asc');

    $firstUserAsc = $component->get('relations')->first()['user_name'];
    expect($firstUserAsc)->toBe('Aaron User');
});

test('admin link appears in wishlist manager account menu for admin only', function () {
    $admin = User::factory()->admin()->create();
    $regularUser = User::factory()->create(['is_admin' => false]);

    $this->actingAs($regularUser);
    Livewire::test(WishlistManager::class)
        ->assertDontSee(__('Admin Panel'));

    $this->actingAs($admin);
    Livewire::test(WishlistManager::class)
        ->assertSee(__('Admin Panel'));
});

test('wishlist user model correctly queries pivot table', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->create();

    $pivot = WishlistUser::create([
        'user_id' => $user->id,
        'wishlist_id' => $wishlist->id,
    ]);

    expect($pivot->exists)->toBeTrue()
        ->and($pivot->user->id)->toBe($user->id)
        ->and($pivot->wishlist->id)->toBe($wishlist->id);
});
