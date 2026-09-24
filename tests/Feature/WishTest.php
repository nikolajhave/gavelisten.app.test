<?php

use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('wish belongs to wishlist', function () {
    $wishlist = Wishlist::factory()->create();
    $wish = Wish::factory()->create([
        'wishlist_id' => $wishlist->id,
        'title' => 'Mechanical Keyboard',
        'description' => 'Keychron Q1 Pro wireless mechanical keyboard',
        'url' => 'https://example.com/keyboard',
        'price' => 199.99,
        'sort_order' => 1,
    ]);

    expect($wish->wishlist->id)->toBe($wishlist->id)
        ->and($wish->title)->toBe('Mechanical Keyboard')
        ->and($wish->description)->toBe('Keychron Q1 Pro wireless mechanical keyboard')
        ->and($wish->url)->toBe('https://example.com/keyboard')
        ->and($wish->price)->toBe('199.99')
        ->and($wish->sort_order)->toBe(1);
});

test('wish supports nullable description, url, and price with default sort_order 0', function () {
    $wishlist = Wishlist::factory()->create();
    $wish = Wish::create([
        'wishlist_id' => $wishlist->id,
        'title' => 'Simple Wish',
    ]);

    expect($wish->title)->toBe('Simple Wish')
        ->and($wish->description)->toBeNull()
        ->and($wish->url)->toBeNull()
        ->and($wish->price)->toBeNull()
        ->and($wish->sort_order)->toBe(0);

    $this->assertDatabaseHas('wishes', [
        'id' => $wish->id,
        'title' => 'Simple Wish',
        'description' => null,
        'url' => null,
        'price' => null,
        'sort_order' => 0,
    ]);
});
