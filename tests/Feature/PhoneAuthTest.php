<?php

use App\Contracts\SmsService;
use App\Livewire\Auth\PhoneAuth;
use App\Models\PhoneVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('login page can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200)
        ->assertSeeLivewire(PhoneAuth::class)
        ->assertSee('Log in or Sign up')
        ->assertSee('Continue with Google');
});

test('authenticated user is redirected away from login', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect('/');
});

test('phone number is required to send otp', function () {
    Livewire::test(PhoneAuth::class)
        ->set('phone', '')
        ->call('sendOtp')
        ->assertHasErrors(['phone' => 'required'])
        ->assertSet('step', 'phone');
});

test('send otp dispatches code and switches to otp step', function () {
    $mockSms = Mockery::mock(SmsService::class);
    $mockSms->shouldReceive('sendOtp')->once()->andReturnTrue();
    $this->app->instance(SmsService::class, $mockSms);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4512345678')
        ->call('sendOtp')
        ->assertHasNoErrors()
        ->assertSet('step', 'otp')
        ->assertSee('Verify Your Phone')
        ->assertSee('A 6-digit code has been sent to +4512345678');

    $this->assertDatabaseHas('phone_verification_codes', [
        'phone' => '+4512345678',
    ]);
});

test('code is required and must be 6 characters to verify otp', function () {
    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4512345678')
        ->set('step', 'otp')
        ->set('code', '123')
        ->call('verifyOtp')
        ->assertHasErrors(['code' => 'size']);
});

test('verify otp with invalid code shows error', function () {
    PhoneVerificationCode::factory()->create([
        'phone' => '+4512345678',
        'code' => '999999',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4512345678')
        ->set('step', 'otp')
        ->set('code', '111111')
        ->call('verifyOtp')
        ->assertHasErrors(['code']);

    $this->assertGuest();
});

test('verify otp logs in user and creates user and wishlist if new', function () {
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
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user->phone)->toBe('+4512345678')
        ->and($user->phone_verified_at)->not->toBeNull()
        ->and($user->wishlists)->toHaveCount(1)
        ->and($user->wishlists->first()->title)->toBe('My Wishlist');
});

test('verify otp logs in existing user', function () {
    $user = User::factory()->phoneOnly()->create([
        'phone' => '+4512345678',
    ]);

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
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

test('resend otp sends a new code', function () {
    $mockSms = Mockery::mock(SmsService::class);
    $mockSms->shouldReceive('sendOtp')->once()->andReturnTrue();
    $this->app->instance(SmsService::class, $mockSms);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4512345678')
        ->set('step', 'otp')
        ->call('resendOtp')
        ->assertSee('A new verification code has been sent');
});

test('edit phone returns component to phone input step', function () {
    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4512345678')
        ->set('step', 'otp')
        ->set('code', '123456')
        ->call('editPhone')
        ->assertSet('step', 'phone')
        ->assertSet('code', '');
});
