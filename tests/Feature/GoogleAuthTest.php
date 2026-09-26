<?php

use App\Models\SocialIdentity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

uses(RefreshDatabase::class);

test('google oauth redirect initiates socialite provider redirect', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('redirect')
        ->once()
        ->andReturn(new SymfonyRedirect('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.redirect'));

    $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('google callback creates a new user, social identity, and auto-provisions wishlist', function () {
    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-unique-id-123');
    $socialiteUser->shouldReceive('getEmail')->andReturn('googleuser@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Google User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('guser');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/');
    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user->email)->toBe('googleuser@example.com')
        ->and($user->name)->toBe('Google User')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->socialIdentities)->toHaveCount(1)
        ->and($user->socialIdentities->first()->provider_name)->toBe('google')
        ->and($user->socialIdentities->first()->provider_id)->toBe('google-unique-id-123')
        ->and($user->wishlists)->toHaveCount(1)
        ->and($user->wishlists->first()->title)->toBe(__('My Wishlist'));
});

test('google callback links social identity to existing user matching email', function () {
    $existingUser = User::factory()->create([
        'name' => null,
        'email' => 'existing@example.com',
        'email_verified_at' => null,
    ]);

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-id-456');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('existing');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($existingUser);

    expect(User::count())->toBe(1)
        ->and($existingUser->fresh()->name)->toBe('Existing User')
        ->and($existingUser->fresh()->email_verified_at)->not->toBeNull()
        ->and($existingUser->socialIdentities)->toHaveCount(1)
        ->and($existingUser->socialIdentities->first()->provider_name)->toBe('google')
        ->and($existingUser->socialIdentities->first()->provider_id)->toBe('google-id-456');
});

test('google callback logs in user when social identity already exists', function () {
    $user = User::factory()->create(['name' => 'Original Name']);
    SocialIdentity::factory()->create([
        'user_id' => $user->id,
        'provider_name' => 'google',
        'provider_id' => 'existing-google-id',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('existing-google-id');
    $socialiteUser->shouldReceive('getEmail')->andReturn('whatever@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('New Name');
    $socialiteUser->shouldReceive('getNickname')->andReturn('whatever');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

test('google callback redirects to login with error on exception', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andThrow(new Exception('Invalid OAuth token'));

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'))
        ->assertSessionHas('error', __('Google authentication failed. Please try again.'));

    $this->assertGuest();
});

test('google callback recovers from orphaned social identity by creating new user', function () {
    DB::commit();
    DB::statement('PRAGMA foreign_keys = OFF;');
    DB::table('social_identities')->insert([
        'user_id' => 99999,
        'provider_name' => 'google',
        'provider_id' => 'orphaned-google-id-1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::statement('PRAGMA foreign_keys = ON;');
    DB::beginTransaction();

    expect(SocialIdentity::where('provider_id', 'orphaned-google-id-1')->first())->not->toBeNull();

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('orphaned-google-id-1');
    $socialiteUser->shouldReceive('getEmail')->andReturn('orphan-new@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Recovered User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('recovered');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/');
    $this->assertAuthenticated();

    $user = auth()->user();
    $identity = SocialIdentity::where('provider_id', 'orphaned-google-id-1')->first();
    expect($user->email)->toBe('orphan-new@example.com')
        ->and($user->name)->toBe('Recovered User')
        ->and($identity)->not->toBeNull()
        ->and($identity->user_id)->toBe($user->id);

    // Clean up committed data
    DB::commit();
    DB::statement('PRAGMA foreign_keys = OFF;');
    DB::table('social_identities')->delete();
    DB::table('users')->delete();
    DB::statement('PRAGMA foreign_keys = ON;');
    DB::beginTransaction();
});

test('google callback recovers from orphaned social identity by linking existing email user', function () {
    $existingUser = User::factory()->create([
        'name' => 'Existing User',
        'email' => 'orphan-existing@example.com',
    ]);

    DB::commit();
    DB::statement('PRAGMA foreign_keys = OFF;');
    DB::table('social_identities')->insert([
        'user_id' => 99999,
        'provider_name' => 'google',
        'provider_id' => 'orphaned-google-id-2',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::statement('PRAGMA foreign_keys = ON;');
    DB::beginTransaction();

    expect(SocialIdentity::where('provider_id', 'orphaned-google-id-2')->first())->not->toBeNull();

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('orphaned-google-id-2');
    $socialiteUser->shouldReceive('getEmail')->andReturn('orphan-existing@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('existing');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($existingUser);

    $identity = SocialIdentity::where('provider_id', 'orphaned-google-id-2')->first();
    expect($identity)->not->toBeNull()
        ->and($identity->user_id)->toBe($existingUser->id);

    // Clean up committed data
    DB::commit();
    DB::statement('PRAGMA foreign_keys = OFF;');
    DB::table('social_identities')->delete();
    DB::table('users')->delete();
    DB::statement('PRAGMA foreign_keys = ON;');
    DB::beginTransaction();
});
