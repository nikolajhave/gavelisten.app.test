<?php

use App\Livewire\Auth\PhoneAuth;
use App\Models\PhoneVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('session lifetime is configured for 1 year (525600 minutes)', function () {
    expect(config('session.lifetime'))->toBe(525600);
});

test('user getAuthPassword returns a string even when password is null', function () {
    $user = User::factory()->passwordless()->create();

    expect($user->password)->toBeNull()
        ->and($user->getAuthPassword())->toBeString()
        ->and($user->getAuthPassword())->toBe('');
});

test('passwordless user is remembered across sessions using remember cookie', function () {
    $user = User::factory()->passwordless()->create();

    // Log in user with remember = true
    Auth::guard('web')->login($user, remember: true);

    $recallerName = Auth::guard('web')->getRecallerName();
    $queuedCookies = Cookie::queued($recallerName);

    expect($queuedCookies)->not->toBeNull();
    $recallerValue = $queuedCookies->getValue();

    // Clear the active session and user from guard memory (simulating session expiration / fresh browser)
    Auth::guard('web')->forgetUser();
    session()->flush();

    $this->assertGuest();

    // Send a request to protected route with the recaller cookie
    $response = $this->withCookie($recallerName, $recallerValue)
        ->get(route('wishlist'));

    $response->assertOk();
    $this->assertAuthenticatedAs($user);
});

test('phone otp verification sets remember cookie for passwordless user', function () {
    PhoneVerificationCode::factory()->create([
        'phone' => '+4512345678',
        'code' => '654321',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4512345678')
        ->set('step', 'otp')
        ->set('code', '654321')
        ->call('verifyOtp')
        ->assertSet('step', 'name')
        ->set('name', 'Remember User')
        ->call('saveName')
        ->assertRedirect('/');

    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user->password)->toBeNull()
        ->and($user->name)->toBe('Remember User');

    $recallerName = Auth::guard('web')->getRecallerName();
    $queuedCookies = Cookie::queued($recallerName);
    expect($queuedCookies)->not->toBeNull();
});

test('google oauth callback sets remember cookie for passwordless user', function () {
    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-123456');
    $socialiteUser->shouldReceive('getEmail')->andReturn('remember@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Remember Me');
    $socialiteUser->shouldReceive('getNickname')->andReturn('remember');

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
    expect($user->password)->toBeNull();

    $recallerName = Auth::guard('web')->getRecallerName();
    $response->assertCookie($recallerName);
});

test('logging out clears remember token and logs out user', function () {
    $user = User::factory()->passwordless()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect('/');
    $this->assertGuest();
});
