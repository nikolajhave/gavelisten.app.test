<?php

use App\Livewire\PublicWishlist;
use App\Livewire\WishlistManager;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('user can add and remove a friend via model methods', function () {
    $userA = User::factory()->create(['name' => 'Alice']);
    $userB = User::factory()->create(['name' => 'Bob']);

    expect($userA->isFriendWith($userB))->toBeFalse();

    $userA->addFriend($userB);

    expect($userA->fresh()->isFriendWith($userB))->toBeTrue()
        ->and($userA->friends()->count())->toBe(1)
        ->and($userA->friends->first()->id)->toBe($userB->id)
        ->and($userB->friendedBy->first()->id)->toBe($userA->id);

    // Adding again should not create duplicate
    $userA->addFriend($userB);
    expect($userA->friends()->count())->toBe(1);

    // Remove friend
    $userA->removeFriend($userB);
    expect($userA->fresh()->isFriendWith($userB))->toBeFalse()
        ->and($userA->friends()->count())->toBe(0);
});

test('user cannot add themselves as friend', function () {
    $user = User::factory()->create(['name' => 'Solo']);

    $user->addFriend($user);

    expect($user->fresh()->friends()->count())->toBe(0)
        ->and($user->isFriendWith($user))->toBeFalse();
});

test('deleting a user removes their friendships from database cascade', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->addFriend($userB);

    $this->assertDatabaseHas('friends', [
        'user_id' => $userA->id,
        'friend_id' => $userB->id,
    ]);

    $userB->delete();

    $this->assertDatabaseMissing('friends', [
        'user_id' => $userA->id,
        'friend_id' => $userB->id,
    ]);
});

test('guest viewing shared wishlist sees add friend prompt linking to login', function () {
    $owner = User::factory()->create();
    $wishlist = Wishlist::factory()->for($owner)->create([
        'share_token' => 'guestfriendtest',
    ]);

    $response = $this->get('/w/guestfriendtest');

    $response->assertStatus(200);
    $response->assertSee(__('Add friend'));
    $response->assertSee(route('login'));
});

test('owner viewing their own shared wishlist does not see add friend button', function () {
    $owner = User::factory()->create();
    $wishlist = Wishlist::factory()->for($owner)->create([
        'share_token' => 'ownlist123',
    ]);

    $response = $this->actingAs($owner)->get('/w/ownlist123');

    $response->assertStatus(200);
    $response->assertDontSee('wire:click="addFriend"', false);
    $response->assertDontSee('wire:click="removeFriend"', false);
});

test('authenticated user can add friend on shared wishlist via livewire', function () {
    $owner = User::factory()->create(['name' => 'Santa Claus']);
    $viewer = User::factory()->create(['name' => 'Elf']);

    $wishlist = Wishlist::factory()->for($owner)->create([
        'title' => 'North Pole Wishes',
        'share_token' => 'northpole2026',
    ]);

    $this->actingAs($viewer);

    Livewire::test(PublicWishlist::class, ['share_token' => 'northpole2026'])
        ->assertSee(__('Add friend'))
        ->assertDontSee(__('Friend added'))
        ->call('addFriend')
        ->assertSee(__('Friend added'))
        ->assertDontSee(__('Add friend'));

    expect($viewer->fresh()->isFriendWith($owner))->toBeTrue();
    $this->assertDatabaseHas('friends', [
        'user_id' => $viewer->id,
        'friend_id' => $owner->id,
    ]);
});

test('authenticated user can remove friend from shared wishlist', function () {
    $owner = User::factory()->create(['name' => 'Old Friend']);
    $viewer = User::factory()->create(['name' => 'Viewer']);

    $viewer->addFriend($owner);

    $wishlist = Wishlist::factory()->for($owner)->create([
        'share_token' => 'oldfriendtoken',
    ]);

    $this->actingAs($viewer);

    Livewire::test(PublicWishlist::class, ['share_token' => 'oldfriendtoken'])
        ->assertSee(__('Friend added'))
        ->call('removeFriend')
        ->assertSee(__('Add friend'))
        ->assertDontSee(__('Friend added'));

    expect($viewer->fresh()->isFriendWith($owner))->toBeFalse();
});

test('wishlist manager displays friends burger menu with empty state when user has no friends', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/wishlist');
    $response->assertStatus(200);
    $response->assertSee(__('Friends'));
    $response->assertSee(__('No friends yet'));

    Livewire::test(WishlistManager::class)
        ->assertSee(__('Friends'))
        ->assertSee(__('No friends yet'));
});

test('wishlist manager burger menu lists friends and links to their wishlists', function () {
    $user = User::factory()->create(['name' => 'Me']);
    $friend1 = User::factory()->create(['name' => 'Casper']);
    $friend2 = User::factory()->create(['name' => 'Freja']);

    $wishlist1 = Wishlist::factory()->for($friend1)->create([
        'title' => 'Caspers Ønsker',
        'share_token' => 'caspertoken',
    ]);

    $wishlist2 = Wishlist::factory()->for($friend2)->create([
        'title' => 'Frejas Ønskeliste',
        'share_token' => 'frejatoken',
    ]);

    $user->addFriend($friend1);
    $user->addFriend($friend2);

    $this->actingAs($user);

    $response = $this->get('/wishlist');
    $response->assertStatus(200);
    $response->assertSee('Casper');
    $response->assertSee('Freja');
    $response->assertSee('Caspers Ønsker');
    $response->assertSee('Frejas Ønskeliste');
    $response->assertSee('/w/caspertoken');
    $response->assertSee('/w/frejatoken');

    Livewire::test(WishlistManager::class)
        ->assertSee('Casper')
        ->assertSee('Freja')
        ->assertSee('Caspers Ønsker')
        ->assertSee('Frejas Ønskeliste');
});

test('user can remove friend directly from wishlist manager burger menu', function () {
    $user = User::factory()->create(['name' => 'Me']);
    $friend = User::factory()->create(['name' => 'Former Friend']);

    Wishlist::factory()->for($friend)->create([
        'title' => 'Former Wishlist',
        'share_token' => 'formertoken',
    ]);

    $user->addFriend($friend);
    expect($user->fresh()->friends()->count())->toBe(1);

    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->assertSee('Former Friend')
        ->call('removeFriend', $friend->id)
        ->assertDontSee('Former Friend')
        ->assertSee(__('No friends yet'));

    expect($user->fresh()->friends()->count())->toBe(0);
});

test('burger menu does not display friend phone number when friend has no name', function () {
    $user = User::factory()->create(['name' => 'Me']);
    $friend = User::factory()->phoneOnly()->create([
        'name' => null,
        'phone' => '+4587654321',
    ]);

    Wishlist::factory()->for($friend)->create([
        'title' => 'Vens Ønsker',
        'share_token' => 'friendphonetoken',
    ]);

    $user->addFriend($friend);

    $this->actingAs($user);

    $response = $this->get('/wishlist');
    $response->assertStatus(200);
    $response->assertDontSee('+4587654321');
    $response->assertDontSee('87654321');

    Livewire::test(WishlistManager::class)
        ->assertDontSee('+4587654321')
        ->assertDontSee('87654321')
        ->assertSee(__('Friend'));
});
