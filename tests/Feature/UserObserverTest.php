<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('newly registered user automatically gets default My Wishlist with share token', function () {
    $user = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    expect($user->wishlists)->toHaveCount(1);

    $wishlist = $user->wishlists->first();

    expect($wishlist->title)->toBe(__('My Wishlist'))
        ->and($wishlist->share_token)->toBeString()
        ->and(strlen($wishlist->share_token))->toBe(12);

    $this->assertDatabaseHas('wishlists', [
        'user_id' => $user->id,
        'title' => __('My Wishlist'),
        'share_token' => $wishlist->share_token,
    ]);
});

test('observer does not duplicate wishlist if one already exists for user', function () {
    $user = User::factory()->make();
    $user->save();

    expect($user->fresh()->wishlists)->toHaveCount(1);
});
