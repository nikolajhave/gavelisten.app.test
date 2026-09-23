<?php

use App\Models\SocialIdentity;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can be created with email and phone', function () {
    $user = User::factory()->create([
        'name' => 'Alice Tester',
        'email' => 'alice@example.com',
        'phone' => '+4512345678',
    ]);

    expect($user->email)->toBe('alice@example.com')
        ->and($user->phone)->toBe('+4512345678');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'alice@example.com',
        'phone' => '+4512345678',
    ]);
});

test('user supports nullable email for phone-only accounts', function () {
    $user = User::factory()->phoneOnly()->create([
        'phone' => '+4587654321',
    ]);

    expect($user->email)->toBeNull()
        ->and($user->phone)->toBe('+4587654321');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => null,
        'phone' => '+4587654321',
    ]);
});

test('user supports nullable phone for email-only accounts', function () {
    $user = User::factory()->emailOnly()->create([
        'email' => 'bob@example.com',
    ]);

    expect($user->phone)->toBeNull()
        ->and($user->email)->toBe('bob@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'bob@example.com',
        'phone' => null,
    ]);
});

test('phone numbers are formatted to E.164 standard', function () {
    $user = User::factory()->create([
        'phone' => '+45 20 30 40 50',
    ]);

    expect($user->phone)->toBe('+4520304050');

    $user->update(['phone' => '0045 11 22 33 44']);
    expect($user->fresh()->phone)->toBe('+4511223344');

    $user->update(['phone' => '+1 (415) 555-2671']);
    expect($user->fresh()->phone)->toBe('+14155552671');
});

test('user has many social identities', function () {
    $user = User::factory()->create();
    $identity1 = SocialIdentity::factory()->google()->create(['user_id' => $user->id]);
    $identity2 = SocialIdentity::factory()->apple()->create(['user_id' => $user->id]);

    expect($user->socialIdentities->pluck('id')->all())
        ->toEqualCanonicalizing([$identity1->id, $identity2->id]);
});

test('user has many wishlists', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->create(['user_id' => $user->id, 'title' => 'Birthday 2026']);

    expect($user->fresh()->wishlists)->toHaveCount(2)
        ->and($user->fresh()->wishlists->pluck('title')->all())->toContain(__('My Wishlist'), 'Birthday 2026')
        ->and($wishlist->user->id)->toBe($user->id);
});

test('password and remember_token are hidden from array serialization', function () {
    $user = User::factory()->create();

    $array = $user->toArray();
    expect($array)->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});
