<?php

use App\Models\User;
use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('wishlist belongs to user', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->create([
        'user_id' => $user->id,
        'title' => 'My Birthday Wishlist',
    ]);

    expect($wishlist->user->id)->toBe($user->id)
        ->and($wishlist->title)->toBe('My Birthday Wishlist')
        ->and($wishlist->share_token)->toBeString();
});

test('wishlist automatically generates a 12-character share_token if not provided', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::create([
        'user_id' => $user->id,
        'title' => 'Christmas 2026',
    ]);

    expect($wishlist->share_token)->not->toBeNull()
        ->and(strlen($wishlist->share_token))->toBe(12);
});

test('wishlist preserves custom share_token if provided', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::create([
        'user_id' => $user->id,
        'title' => 'Custom Token List',
        'share_token' => 'custom-token-xyz',
    ]);

    expect($wishlist->share_token)->toBe('custom-token-xyz');
});

test('wishlist enforces unique share_token', function () {
    $user = User::factory()->create();
    Wishlist::factory()->create([
        'user_id' => $user->id,
        'share_token' => 'unique-token-123',
    ]);

    expect(fn () => Wishlist::factory()->create([
        'user_id' => $user->id,
        'share_token' => 'unique-token-123',
    ]))->toThrow(QueryException::class);
});

test('wishlist has many wishes and cascades on delete', function () {
    $wishlist = Wishlist::factory()->create();
    $wish1 = Wish::factory()->create(['wishlist_id' => $wishlist->id, 'title' => 'Lego Set']);
    $wish2 = Wish::factory()->create(['wishlist_id' => $wishlist->id, 'title' => 'Headphones']);

    expect($wishlist->wishes)->toHaveCount(2)
        ->and($wishlist->wishes->pluck('title')->all())->toContain('Lego Set', 'Headphones');

    $wishlist->delete();

    $this->assertDatabaseMissing('wishes', ['id' => $wish1->id]);
    $this->assertDatabaseMissing('wishes', ['id' => $wish2->id]);
});

test('wishlist supports delegated users and canBeEditedBy checks', function () {
    $owner = User::factory()->create(['name' => 'Child']);
    $parent = User::factory()->create(['name' => 'Parent']);
    $other = User::factory()->create(['name' => 'Stranger']);

    $wishlist = $owner->wishlists()->first();

    expect($wishlist->canBeEditedBy($owner))->toBeTrue()
        ->and($wishlist->canBeEditedBy($parent))->toBeFalse()
        ->and($wishlist->canBeEditedBy($other))->toBeFalse();

    $wishlist->delegatedUsers()->attach($parent->id);

    expect($wishlist->fresh()->canBeEditedBy($parent))->toBeTrue()
        ->and($wishlist->fresh()->canBeEditedBy($other))->toBeFalse();
});

test('user allEditableWishlists returns owned and delegated wishlists', function () {
    $parent = User::factory()->create(['name' => 'Parent']);
    $child1 = User::factory()->create(['name' => 'Child 1']);
    $child2 = User::factory()->create(['name' => 'Child 2']);

    $parentWishlist = $parent->wishlists()->first();
    $child1Wishlist = $child1->wishlists()->first();
    $child2Wishlist = $child2->wishlists()->first();

    expect($parent->allEditableWishlists()->pluck('id')->all())->toBe([$parentWishlist->id]);

    $child1Wishlist->delegatedUsers()->attach($parent->id);

    $editableIds = $parent->allEditableWishlists()->pluck('id')->all();
    expect($editableIds)->toContain($parentWishlist->id, $child1Wishlist->id)
        ->and($editableIds)->not->toContain($child2Wishlist->id);
});
