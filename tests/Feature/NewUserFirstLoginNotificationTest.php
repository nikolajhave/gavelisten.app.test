<?php

use App\Livewire\Auth\PhoneAuth;
use App\Models\PhoneVerificationCode;
use App\Models\SocialIdentity;
use App\Models\User;
use App\Notifications\NewUserFirstLoginNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('google oauth new user signup triggers notification to admin email and sets last_login_at', function () {
    Notification::fake();

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-unique-id-999');
    $socialiteUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('New Google User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('newuser');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/');
    $this->assertAuthenticated();

    $user = User::query()->where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->last_login_at)->not->toBeNull();

    Notification::assertSentTo(
        new AnonymousNotifiable,
        NewUserFirstLoginNotification::class,
        function ($notification, $channels, $notifiable) use ($user) {
            return $notifiable->routes['mail'] === 'nikolaj.have@gmail.com'
                && $notification->user->id === $user->id;
        }
    );
});

test('google oauth existing legacy user first login triggers notification to admin', function () {
    Notification::fake();

    $legacyUser = User::factory()->create([
        'name' => 'Legacy User',
        'email' => 'legacy@example.com',
        'last_login_at' => null,
    ]);

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-id-legacy');
    $socialiteUser->shouldReceive('getEmail')->andReturn('legacy@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Legacy User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('legacy');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($legacyUser);

    expect($legacyUser->fresh()->last_login_at)->not->toBeNull();

    Notification::assertSentTo(
        new AnonymousNotifiable,
        NewUserFirstLoginNotification::class,
        function ($notification, $channels, $notifiable) use ($legacyUser) {
            return $notifiable->routes['mail'] === 'nikolaj.have@gmail.com'
                && $notification->user->id === $legacyUser->id;
        }
    );
});

test('google oauth returning user with existing last_login_at does not trigger notification', function () {
    Notification::fake();

    $user = User::factory()->create([
        'name' => 'Returning User',
        'last_login_at' => now()->subDays(2),
    ]);

    SocialIdentity::factory()->create([
        'user_id' => $user->id,
        'provider_name' => 'google',
        'provider_id' => 'existing-google-id-123',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUserContract::class);
    $socialiteUser->shouldReceive('getId')->andReturn('existing-google-id-123');
    $socialiteUser->shouldReceive('getEmail')->andReturn('returning@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Returning User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('returning');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->once()
        ->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);

    Notification::assertNothingSent();
});

test('phone otp new user signup triggers notification to admin email and sets last_login_at', function () {
    Notification::fake();

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
        ->assertSet('step', 'name');

    $user = User::query()->where('phone', '+4512345678')->first();
    expect($user)->not->toBeNull()
        ->and($user->last_login_at)->not->toBeNull();

    Notification::assertSentTo(
        new AnonymousNotifiable,
        NewUserFirstLoginNotification::class,
        function ($notification, $channels, $notifiable) use ($user) {
            return $notifiable->routes['mail'] === 'nikolaj.have@gmail.com'
                && $notification->user->id === $user->id;
        }
    );
});

test('phone otp existing legacy user first login triggers notification to admin', function () {
    Notification::fake();

    $legacyUser = User::factory()->phoneOnly()->create([
        'name' => 'Legacy Nikolaj',
        'phone' => '+4520231120',
        'legacy_id' => 8,
        'last_login_at' => null,
    ]);

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

    expect($legacyUser->fresh()->last_login_at)->not->toBeNull();

    Notification::assertSentTo(
        new AnonymousNotifiable,
        NewUserFirstLoginNotification::class,
        function ($notification, $channels, $notifiable) use ($legacyUser) {
            return $notifiable->routes['mail'] === 'nikolaj.have@gmail.com'
                && $notification->user->id === $legacyUser->id;
        }
    );
});

test('phone otp returning user with existing last_login_at does not trigger notification', function () {
    Notification::fake();

    $user = User::factory()->phoneOnly()->create([
        'name' => 'Returning Phone User',
        'phone' => '+4520231120',
        'last_login_at' => now()->subDay(),
    ]);

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

    Notification::assertNothingSent();
});

test('notification email recipient can be customized via config', function () {
    Notification::fake();
    Config::set('mail.admin_address', 'custom.admin@example.com');

    PhoneVerificationCode::factory()->create([
        'phone' => '+4587654321',
        'code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4587654321')
        ->set('step', 'otp')
        ->set('code', '123456')
        ->call('verifyOtp');

    Notification::assertSentTo(
        new AnonymousNotifiable,
        NewUserFirstLoginNotification::class,
        function ($notification, $channels, $notifiable) {
            return $notifiable->routes['mail'] === 'custom.admin@example.com';
        }
    );
});

test('notification is not sent if admin email is not configured', function () {
    Notification::fake();
    Config::set('mail.admin_address', null);
    Config::set('services.admin_notifications.email', null);

    PhoneVerificationCode::factory()->create([
        'phone' => '+4587654321',
        'code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('phone', '+4587654321')
        ->set('step', 'otp')
        ->set('code', '123456')
        ->call('verifyOtp');

    Notification::assertNothingSent();
});

test('notification mail message contains all user details and links', function () {
    $user = User::factory()->create([
        'name' => 'Tester User',
        'email' => 'tester@example.com',
        'phone' => '+4512345678',
        'legacy_id' => 42,
    ]);

    $notification = new NewUserFirstLoginNotification($user);
    $mail = $notification->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toContain('Tester User')
        ->and($mail->introLines)->toContain('Navn: Tester User')
        ->and($mail->introLines)->toContain('E-mail: tester@example.com')
        ->and($mail->introLines)->toContain('Telefon: +4512345678')
        ->and($mail->introLines)->toContain('Legacy-ID: 42')
        ->and($mail->actionText)->toBe('Se gaveliste');
});
