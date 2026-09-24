<?php

use App\Livewire\PublicWishlist;
use App\Livewire\WishlistManager;
use App\Models\User;
use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('formatted_price attribute formats prices without decimal digits correctly without 00 and without dot', function () {
    $wishWhole = new Wish(['price' => 260.00]);
    expect($wishWhole->formatted_price)->toBe('260 kr');

    $wishLargeWhole = new Wish(['price' => 6499.00]);
    expect($wishLargeWhole->formatted_price)->toBe('6.499 kr');

    $wishZero = new Wish(['price' => 0.00]);
    expect($wishZero->formatted_price)->toBe('0 kr');
});

test('formatted_price attribute formats prices with decimal digits preserving decimals and without dot on kr', function () {
    $wishDecimals = new Wish(['price' => 260.50]);
    expect($wishDecimals->formatted_price)->toBe('260,50 kr');

    $wishCents = new Wish(['price' => 260.05]);
    expect($wishCents->formatted_price)->toBe('260,05 kr');

    $wishLargeDecimals = new Wish(['price' => 6499.95]);
    expect($wishLargeDecimals->formatted_price)->toBe('6.499,95 kr');
});

test('formatted_price attribute returns null when price is null', function () {
    $wish = new Wish(['price' => null]);
    expect($wish->formatted_price)->toBeNull();
});

test('wishlist manager displays whole prices without 00 and with kr without dot', function () {
    $user = User::factory()->create();
    $wishlist = $user->wishlists()->first();

    Wish::factory()->for($wishlist)->create([
        'title' => 'Bordlampe',
        'price' => 260.00,
    ]);
    Wish::factory()->for($wishlist)->create([
        'title' => 'Kaffekande',
        'price' => 260.50,
    ]);

    $this->actingAs($user);

    $response = $this->get('/wishlist');
    $response->assertStatus(200);
    $response->assertSee('260 kr');
    $response->assertSee('260,50 kr');
    $response->assertDontSee('260,00 kr.');
    $response->assertDontSee('260,00 kr');
    $response->assertDontSee('260,50 kr.');

    Livewire::test(WishlistManager::class)
        ->assertSee('260 kr')
        ->assertSee('260,50 kr')
        ->assertDontSee('260,00 kr.')
        ->assertDontSee('260,00 kr')
        ->assertDontSee('260,50 kr.');
});

test('public wishlist displays whole prices without 00 and with kr without dot', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->for($user)->create([
        'title' => 'Fødselsdagsønsker',
        'share_token' => 'pricechecktoken',
    ]);

    Wish::factory()->for($wishlist)->create([
        'title' => 'Gavekort',
        'price' => 260.00,
    ]);
    Wish::factory()->for($wishlist)->create([
        'title' => 'Vinterjakke',
        'price' => 1299.75,
    ]);

    $response = $this->get('/w/pricechecktoken');
    $response->assertStatus(200);
    $response->assertSee('260 kr');
    $response->assertSee('1.299,75 kr');
    $response->assertDontSee('260,00 kr.');
    $response->assertDontSee('260,00 kr');
    $response->assertDontSee('1.299,75 kr.');

    Livewire::test(PublicWishlist::class, ['share_token' => 'pricechecktoken'])
        ->assertSee('260 kr')
        ->assertSee('1.299,75 kr')
        ->assertDontSee('260,00 kr.')
        ->assertDontSee('260,00 kr')
        ->assertDontSee('1.299,75 kr.');
});
