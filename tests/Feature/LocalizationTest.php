<?php

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

test('default application locale is configured as danish with english fallback', function () {
    expect(config('app.locale'))->toBe('da')
        ->and(config('app.fallback_locale'))->toBe('en');
});

test('danish translations are returned for key ui strings and pluralization', function () {
    App::setLocale('da');

    expect(__('My Wishlist'))->toBe('Min ønskeliste')
        ->and(__('Add Wish'))->toBe('Tilføj ønske')
        ->and(__('Create Your Wishlist'))->toBe('Opret din ønskeliste')
        ->and(__('Sign out'))->toBe('Log ud')
        ->and(__('Log in or Sign up'))->toBe('Log ind eller opret profil')
        ->and(__('Verify Your Phone'))->toBe('Bekræft dit telefonnummer')
        ->and(__('Phone Number'))->toBe('Telefonnummer')
        ->and(__('Continue with Phone'))->toBe('Fortsæt med telefon')
        ->and(__('Continue with Google'))->toBe('Fortsæt med Google')
        ->and(__('Verification Code'))->toBe('Bekræftelseskode')
        ->and(__('Verify and Continue'))->toBe('Bekræft og fortsæt')
        ->and(__('Wishlist Manager'))->toBe('Administrer ønskeliste')
        ->and(__('No wishes yet'))->toBe('Ingen ønsker endnu')
        ->and(__('No wishes on this list yet'))->toBe('Ingen ønsker på denne liste endnu')
        ->and(__('See Product'))->toBe('Se produkt')
        ->and(__('Share Wishlist'))->toBe('Del ønskeliste')
        ->and(__('Link Copied!'))->toBe('Link kopieret!')
        ->and(__('The verification code is invalid or has expired.'))->toBe('Bekræftelseskoden er ugyldig eller udløbet.')
        ->and(__('Google authentication failed. Please try again.'))->toBe('Google-godkendelse mislykkedes. Prøv venligst igen.')
        ->and(__('Edit wishlist title'))->toBe('Rediger ønskelistens titel')
        ->and(__('Wishlist title'))->toBe('Ønskelistens titel')
        ->and(__('Save'))->toBe('Gem')
        ->and(trans_choice(':count wish|:count wishes', 1))->toBe('1 ønske')
        ->and(trans_choice(':count wish|:count wishes', 2))->toBe('2 ønsker');
});

test('english translations are preserved and returned when locale is english', function () {
    App::setLocale('en');

    expect(__('My Wishlist'))->toBe('My Wishlist')
        ->and(__('Add Wish'))->toBe('Add Wish')
        ->and(__('Create Your Wishlist'))->toBe('Create Your Wishlist')
        ->and(__('Sign out'))->toBe('Sign out')
        ->and(__('Log in or Sign up'))->toBe('Log in or Sign up')
        ->and(__('Verify Your Phone'))->toBe('Verify Your Phone')
        ->and(__('Phone Number'))->toBe('Phone Number')
        ->and(__('Continue with Phone'))->toBe('Continue with Phone')
        ->and(__('Continue with Google'))->toBe('Continue with Google')
        ->and(__('Verification Code'))->toBe('Verification Code')
        ->and(__('Verify and Continue'))->toBe('Verify and Continue')
        ->and(__('Wishlist Manager'))->toBe('Wishlist Manager')
        ->and(__('No wishes yet'))->toBe('No wishes yet')
        ->and(__('No wishes on this list yet'))->toBe('No wishes on this list yet')
        ->and(__('See Product'))->toBe('See Product')
        ->and(__('Share Wishlist'))->toBe('Share Wishlist')
        ->and(__('Link Copied!'))->toBe('Link Copied!')
        ->and(__('The verification code is invalid or has expired.'))->toBe('The verification code is invalid or has expired.')
        ->and(__('Google authentication failed. Please try again.'))->toBe('Google authentication failed. Please try again.')
        ->and(__('Edit wishlist title'))->toBe('Edit wishlist title')
        ->and(__('Wishlist title'))->toBe('Wishlist title')
        ->and(__('Save'))->toBe('Save')
        ->and(trans_choice(':count wish|:count wishes', 1))->toBe('1 wish')
        ->and(trans_choice(':count wish|:count wishes', 2))->toBe('2 wishes');
});

test('danish validation attributes and messages are active in danish locale', function () {
    App::setLocale('da');

    expect(__('validation.attributes.phone'))->toBe('telefonnummer')
        ->and(__('validation.attributes.code'))->toBe('bekræftelseskode')
        ->and(__('validation.attributes.title'))->toBe('titel')
        ->and(__('validation.attributes.price'))->toBe('pris')
        ->and(__('validation.attributes.url'))->toBe('link')
        ->and(__('validation.required', ['attribute' => 'titel']))->toBe('titel feltet er påkrævet.');
});

test('english validation attributes and messages are active in english locale', function () {
    App::setLocale('en');

    expect(__('validation.attributes.phone'))->toBe('phone number')
        ->and(__('validation.attributes.code'))->toBe('verification code')
        ->and(__('validation.attributes.title'))->toBe('title')
        ->and(__('validation.attributes.price'))->toBe('price')
        ->and(__('validation.attributes.url'))->toBe('link')
        ->and(__('validation.required', ['attribute' => 'title']))->toBe('The title field is required.');
});

test('login page renders danish texts by default and english texts when locale changed', function () {
    App::setLocale('da');

    $responseDa = $this->get('/login');
    $responseDa->assertStatus(200);
    $responseDa->assertSee('Log ind eller opret profil');
    $responseDa->assertSee('Telefonnummer');
    $responseDa->assertSee('Fortsæt med telefon');
    $responseDa->assertSee('Fortsæt med Google');

    App::setLocale('en');

    $responseEn = $this->get('/login');
    $responseEn->assertStatus(200);
    $responseEn->assertSee('Log in or Sign up');
    $responseEn->assertSee('Phone Number');
    $responseEn->assertSee('Continue with Phone');
    $responseEn->assertSee('Continue with Google');
});

test('wishlist manager renders in danish by default and english when locale changed', function () {
    $user = User::factory()->create();

    App::setLocale('da');
    $responseDa = $this->actingAs($user)->get('/wishlist');
    $responseDa->assertStatus(200);
    $responseDa->assertSee('Min ønskeliste');
    $responseDa->assertSee('Tilføj ønske');
    $responseDa->assertSee('Ingen ønsker endnu');

    App::setLocale('en');
    $responseEn = $this->actingAs($user)->get('/wishlist');
    $responseEn->assertStatus(200);
    $responseEn->assertSee('Add Wish');
    $responseEn->assertSee('No wishes yet');
});

test('public wishlist renders in danish by default and english when locale changed', function () {
    $user = User::factory()->create();
    $wishlist = Wishlist::factory()->for($user)->create([
        'title' => 'Fødselsdag 2026',
        'share_token' => 'danishtoken1',
    ]);

    App::setLocale('da');
    $responseDa = $this->get('/w/danishtoken1');
    $responseDa->assertStatus(200);
    $responseDa->assertSee('Fødselsdag 2026');
    $responseDa->assertSee('Opret din ønskeliste');
    $responseDa->assertSee('Ingen ønsker på denne liste endnu');
    $responseDa->assertSee('Del ønskeliste');

    App::setLocale('en');
    $responseEn = $this->get('/w/danishtoken1');
    $responseEn->assertStatus(200);
    $responseEn->assertSee('Fødselsdag 2026');
    $responseEn->assertSee('Create Your Wishlist');
    $responseEn->assertSee('No wishes on this list yet');
    $responseEn->assertSee('Share Wishlist');
});
