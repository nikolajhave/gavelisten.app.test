<?php

use App\Livewire\Auth\PhoneAuth;
use App\Livewire\WishlistManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest visiting home page sees the login page', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSeeLivewire(PhoneAuth::class)
        ->assertSee(__('Log in or Sign up'))
        ->assertSee(__('Continue with Google'))
        ->assertDontSee('Ups - Gavelisten er lige gået i stykker');
});

test('authenticated user visiting home page sees the wishlist manager', function () {
    $user = User::factory()->create([
        'name' => 'Nikolaj',
    ]);

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200)
        ->assertSeeLivewire(WishlistManager::class)
        ->assertSee('Nikolaj')
        ->assertSee(__('My Wishlist'))
        ->assertSee(__('Add Wish'))
        ->assertDontSee('Ups - Gavelisten er lige gået i stykker');
});
