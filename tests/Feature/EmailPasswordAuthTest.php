<?php

use App\Livewire\Auth\PhoneAuth;
use App\Models\User;
use App\Notifications\NewUserFirstLoginNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('login page displays phone and email login options', function () {
    $response = $this->get('/login');

    $response->assertStatus(200)
        ->assertSeeLivewire(PhoneAuth::class)
        ->assertSee(__('Phone'))
        ->assertSee(__('Email'));
});

test('user can switch between phone and email auth modes', function () {
    Livewire::test(PhoneAuth::class)
        ->assertSet('authMode', 'phone')
        ->call('setAuthMode', 'email')
        ->assertSet('authMode', 'email')
        ->assertSee(__('Continue with Email'))
        ->call('setAuthMode', 'phone')
        ->assertSet('authMode', 'phone')
        ->assertSee(__('Continue with Phone'));
});

test('email and password are required to login with email', function () {
    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', '')
        ->set('password', '')
        ->call('loginWithEmail')
        ->assertHasErrors([
            'email' => 'required',
            'password' => 'required',
        ]);

    $this->assertGuest();
});

test('email must be a valid email format', function () {
    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'not-an-email')
        ->set('password', 'secret123')
        ->call('loginWithEmail')
        ->assertHasErrors(['email' => 'email']);

    $this->assertGuest();
});

test('user cannot login with non-existent email', function () {
    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'nonexistent@example.com')
        ->set('password', 'wrongpassword')
        ->call('loginWithEmail')
        ->assertHasErrors(['email'])
        ->assertSee(__('auth.failed'));

    $this->assertGuest();
});

test('user cannot login with wrong password', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('correct-password'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'john@example.com')
        ->set('password', 'wrong-password')
        ->call('loginWithEmail')
        ->assertHasErrors(['email'])
        ->assertSee(__('auth.failed'));

    $this->assertGuest();
});

test('passwordless user cannot login with a password', function () {
    User::factory()->passwordless()->create([
        'email' => 'passwordless@example.com',
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'passwordless@example.com')
        ->set('password', 'any-password')
        ->call('loginWithEmail')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

test('user can login successfully with email and password and redirects to home', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('my-secret-password'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'john@example.com')
        ->set('password', 'my-secret-password')
        ->call('loginWithEmail')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

test('email is trimmed before authentication attempt', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('my-secret-password'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', '  john@example.com  ')
        ->set('password', 'my-secret-password')
        ->call('loginWithEmail')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

test('email login sets remember cookie', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('my-secret-password'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'john@example.com')
        ->set('password', 'my-secret-password')
        ->call('loginWithEmail')
        ->assertHasNoErrors();

    $this->assertAuthenticated();

    $recallerName = Auth::guard('web')->getRecallerName();
    $queuedCookies = Cookie::queued($recallerName);
    expect($queuedCookies)->not->toBeNull();
});

test('user without name transitions to name step on email login', function () {
    User::factory()->create([
        'name' => '',
        'email' => 'noname@example.com',
        'password' => Hash::make('my-secret-password'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'noname@example.com')
        ->set('password', 'my-secret-password')
        ->call('loginWithEmail')
        ->assertHasNoErrors()
        ->assertSet('step', 'name')
        ->assertSee(__('What should we call you?'))
        ->set('name', 'Named User')
        ->call('saveName')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticated();
    expect(auth()->user()->name)->toBe('Named User');
});

test('login attempts are throttled after too many failed attempts', function () {
    RateLimiter::clear(RateLimiter::availableIn('throttle'));

    $component = Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'throttle@example.com')
        ->set('password', 'wrong-pass');

    for ($i = 0; $i < 5; $i++) {
        $component->call('loginWithEmail')
            ->assertHasErrors(['email']);
    }

    $component->call('loginWithEmail')
        ->assertHasErrors(['email']);
});

test('first login notification is triggered on first login via email and password', function () {
    config(['mail.admin_address' => 'admin@example.com']);
    Notification::fake();

    $user = User::factory()->create([
        'name' => 'First Timer',
        'email' => 'first@example.com',
        'password' => Hash::make('secret123'),
        'last_login_at' => null,
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'first@example.com')
        ->set('password', 'secret123')
        ->call('loginWithEmail')
        ->assertHasNoErrors();

    Notification::assertSentOnDemand(
        NewUserFirstLoginNotification::class,
        function ($notification, $channels, $notifiable) use ($user) {
            return $notifiable->routes['mail'] === 'admin@example.com'
                && $notification->user->id === $user->id;
        }
    );

    expect($user->fresh()->last_login_at)->not->toBeNull();
});
