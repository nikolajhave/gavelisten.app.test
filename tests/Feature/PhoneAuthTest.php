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
        ->assertSee('<title>Gavelisten</title>', false)
        ->assertSee('Gavelisten')
        ->assertSee('Velkommen til den nye udgave af Gavelisten')
        ->assertSee('20231120')
        ->assertSee(__('Log in or Sign up'))
        ->assertSee(__('Continue with Google'));
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
        ->assertSee(__('Verify Your Phone'))
        ->assertSee(__('A 6-digit code has been sent to :phone.', ['phone' => '+4512345678']));

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

test('verify otp logs in user and transitions to name step if new', function () {
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
        ->assertSet('step', 'name')
        ->assertSee(__('What should we call you?'))
        ->assertSee(__('Enter your name so friends and family can recognize your wishlist when sharing.'))
        ->set('name', 'Nikolaj')
        ->call('saveName')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user->phone)->toBe('+4512345678')
        ->and($user->name)->toBe('Nikolaj')
        ->and($user->phone_verified_at)->not->toBeNull()
        ->and($user->wishlists)->toHaveCount(1)
        ->and($user->wishlists->first()->title)->toBe(__('My Wishlist'));
});

test('name is required and validated when saving name', function () {
    $user = User::factory()->phoneOnly()->create([
        'phone' => '+4512345678',
        'name' => null,
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
        ->assertSet('step', 'name')
        ->set('name', '')
        ->call('saveName')
        ->assertHasErrors(['name' => 'required'])
        ->set('name', 'A')
        ->call('saveName')
        ->assertHasErrors(['name' => 'min'])
        ->set('name', 'Nikolaj')
        ->call('saveName')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh()->name)->toBe('Nikolaj');
});

test('verify otp logs in existing user with name and redirects immediately', function () {
    $user = User::factory()->phoneOnly()->create([
        'phone' => '+4512345678',
        'name' => 'Existing User',
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

test('send otp accepts 8-digit danish phone number without country code', function () {
    $mockSms = Mockery::mock(SmsService::class);
    $mockSms->shouldReceive('sendOtp')->with('+4520231120', Mockery::any())->once()->andReturnTrue();
    $this->app->instance(SmsService::class, $mockSms);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '20231120')
        ->call('sendOtp')
        ->assertHasNoErrors()
        ->assertSet('step', 'otp');

    $this->assertDatabaseHas('phone_verification_codes', [
        'phone' => '+4520231120',
    ]);
});

test('send otp accepts phone number with spaces and formatting', function () {
    $mockSms = Mockery::mock(SmsService::class);
    $mockSms->shouldReceive('sendOtp')->with('+4520231120', Mockery::any())->once()->andReturnTrue();
    $this->app->instance(SmsService::class, $mockSms);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '20 23 11 20')
        ->call('sendOtp')
        ->assertHasNoErrors()
        ->assertSet('step', 'otp');

    $this->assertDatabaseHas('phone_verification_codes', [
        'phone' => '+4520231120',
    ]);
});

test('20231120 and +4520231120 log into the exact same user account', function () {
    // 1. User originally created via 8-digit phone without +45
    $user = User::factory()->phoneOnly()->create([
        'phone' => '20231120',
    ]);

    expect($user->phone)->toBe('+4520231120');

    // 2. User logs in entering +4520231120
    PhoneVerificationCode::factory()->create([
        'phone' => '+4520231120',
        'code' => '654321',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4520231120')
        ->set('step', 'otp')
        ->set('code', '654321')
        ->call('verifyOtp')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);

    auth()->logout();

    // 3. User logs in entering 20231120 without +45
    PhoneVerificationCode::factory()->create([
        'phone' => '20231120',
        'code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '20231120')
        ->set('step', 'otp')
        ->set('code', '123456')
        ->call('verifyOtp')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    expect(User::where('phone', '+4520231120')->count())->toBe(1);
});

test('resend otp sends a new code', function () {
    $mockSms = Mockery::mock(SmsService::class);
    $mockSms->shouldReceive('sendOtp')->once()->andReturnTrue();
    $this->app->instance(SmsService::class, $mockSms);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4512345678')
        ->set('step', 'otp')
        ->call('resendOtp')
        ->assertSee(__('A new verification code has been sent to :phone.', ['phone' => '+4512345678']));
});

test('edit phone returns component to phone input step', function () {
    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4512345678')
        ->set('step', 'otp')
        ->set('code', '123456')
        ->set('name', 'Test Name')
        ->set('statusMessage', 'Some message')
        ->call('editPhone')
        ->assertSet('step', 'phone')
        ->assertSet('code', '')
        ->assertSet('name', '')
        ->assertSet('statusMessage', null);
});

test('saving name trims surrounding whitespace', function () {
    $user = User::factory()->phoneOnly()->create([
        'phone' => '+4512345678',
        'name' => null,
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
        ->assertSet('step', 'name')
        ->set('name', '   Nikolaj Jensen   ')
        ->call('saveName')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh()->name)->toBe('Nikolaj Jensen');
});
