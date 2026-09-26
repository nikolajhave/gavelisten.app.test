<?php

use App\Livewire\PublicWishlist;
use App\Livewire\WishlistManager;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('authenticated user can open and close profile modal', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+4512345678',
    ]);
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->assertSet('showProfileModal', false)
        ->call('openProfileModal')
        ->assertSet('showProfileModal', true)
        ->assertSet('profileName', 'John Doe')
        ->assertSet('profileEmail', 'john@example.com')
        ->assertSet('profilePhone', '+4512345678')
        ->call('closeProfileModal')
        ->assertSet('showProfileModal', false);
});

test('authenticated user can update their name, email, and phone', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'phone' => '+4511223344',
    ]);
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openProfileModal')
        ->set('profileName', 'New Name')
        ->set('profileEmail', 'new@example.com')
        ->set('profilePhone', '99887766')
        ->call('saveProfile')
        ->assertSet('showProfileModal', false)
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@example.com')
        ->and($user->phone)->toBe('+4599887766');
});

test('user can update their password via profile modal', function () {
    $user = User::factory()->create([
        'name' => 'Password User',
        'email' => 'pwd@example.com',
        'password' => Hash::make('old-secret-password'),
    ]);
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openProfileModal')
        ->set('profilePassword', 'new-super-secret-password')
        ->call('saveProfile')
        ->assertSet('showProfileModal', false)
        ->assertHasNoErrors();

    $user->refresh();

    expect(Hash::check('new-super-secret-password', $user->password))->toBeTrue();
});

test('leaving password blank keeps existing password unchanged', function () {
    $originalPasswordHash = Hash::make('existing-password');
    $user = User::factory()->create([
        'name' => 'No Password Change',
        'email' => 'nopwd@example.com',
        'password' => $originalPasswordHash,
    ]);
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openProfileModal')
        ->set('profileName', 'Updated Name Only')
        ->set('profilePassword', '')
        ->call('saveProfile')
        ->assertSet('showProfileModal', false)
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated Name Only')
        ->and(Hash::check('existing-password', $user->password))->toBeTrue();
});

test('validates required name and email format on profile modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openProfileModal')
        ->set('profileName', '')
        ->set('profileEmail', 'invalid-email-address')
        ->set('profilePassword', '123') // min 6
        ->call('saveProfile')
        ->assertHasErrors([
            'profileName' => 'required',
            'profileEmail' => 'email',
            'profilePassword' => 'min',
        ]);
});

test('prevents taking an email that belongs to another user', function () {
    $otherUser = User::factory()->create([
        'email' => 'taken@example.com',
    ]);
    $user = User::factory()->create([
        'email' => 'current@example.com',
    ]);
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openProfileModal')
        ->set('profileEmail', 'taken@example.com')
        ->call('saveProfile')
        ->assertHasErrors(['profileEmail' => 'unique']);
});

test('prevents taking a phone number that belongs to another user', function () {
    $otherUser = User::factory()->create([
        'phone' => '+4512345678',
    ]);
    $user = User::factory()->create([
        'phone' => '+4587654321',
    ]);
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openProfileModal')
        ->set('profilePhone', '12345678')
        ->call('saveProfile')
        ->assertHasErrors(['profilePhone']);
});

test('allows keeping own existing email and phone during profile save', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'keepme@example.com',
        'phone' => '+4520304050',
    ]);
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->call('openProfileModal')
        ->set('profileName', 'New Name Keep Email')
        ->set('profileEmail', 'keepme@example.com')
        ->set('profilePhone', '+4520304050')
        ->call('saveProfile')
        ->assertSet('showProfileModal', false)
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('New Name Keep Email')
        ->and($user->email)->toBe('keepme@example.com')
        ->and($user->phone)->toBe('+4520304050');
});

test('wishlist manager menu renders user profile card, friends, and sign out', function () {
    $user = User::factory()->create([
        'name' => 'Frederik Test',
        'email' => 'frederik@example.com',
    ]);
    $friend = User::factory()->create([
        'name' => 'Maja Friend',
    ]);
    $user->addFriend($friend);
    $this->actingAs($user);

    Livewire::test(WishlistManager::class)
        ->assertSee('Frederik Test')
        ->assertSee('frederik@example.com')
        ->assertSee(__('Profile'))
        ->assertSee(__('Friends'))
        ->assertSee('Maja Friend')
        ->assertSee(__('Sign out'))
        ->assertSeeHtml('wire:click="openProfileModal"');
});

test('public wishlist view renders account menu when authenticated', function () {
    $owner = User::factory()->create(['name' => 'Owner Person']);
    $viewer = User::factory()->create([
        'name' => 'Viewer Person',
        'email' => 'viewer@example.com',
    ]);
    $wishlist = Wishlist::factory()->for($owner)->create([
        'share_token' => 'pubviewertest',
    ]);
    $this->actingAs($viewer);

    Livewire::test(PublicWishlist::class, ['share_token' => 'pubviewertest'])
        ->assertSee('Viewer Person')
        ->assertSee('viewer@example.com')
        ->assertSee(__('My Wishlist'))
        ->assertSee(__('Friends'))
        ->assertSee(__('Sign out'));
});
