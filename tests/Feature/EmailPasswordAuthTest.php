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

test('login page displays phone and email login options with single email input on initial screen', function () {
    $response = $this->get('/login');

    $response->assertStatus(200)
        ->assertSeeLivewire(PhoneAuth::class)
        ->assertSee(__('Phone'))
        ->assertSee(__('Email'));
});

test('user can switch between phone and email auth modes', function () {
    Livewire::test(PhoneAuth::class)
        ->assertSet('authMode', 'phone')
        ->assertSee(__('Continue with Phone'))
        ->call('setAuthMode', 'email')
        ->assertSet('authMode', 'email')
        ->assertSee(__('Continue with Email'))
        ->assertSeeHtml('autocomplete="username"')
        ->assertDontSee(__('Password'))
        ->call('setAuthMode', 'phone')
        ->assertSet('authMode', 'phone')
        ->assertSee(__('Continue with Phone'));
});

test('email is required and must be valid format to continue with email', function () {
    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', '')
        ->call('continueWithEmail')
        ->assertHasErrors(['email' => 'required']);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'not-an-email')
        ->call('continueWithEmail')
        ->assertHasErrors(['email' => 'email']);

    $this->assertGuest();
});

test('entering existing user email transitions to password step', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('secret123'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'john@example.com')
        ->call('continueWithEmail')
        ->assertHasNoErrors()
        ->assertSet('step', 'email_password')
        ->assertSee(__('Log In'))
        ->assertSee(__('Password'))
        ->assertSee(__('Change email'))
        ->assertSeeHtml('<input type="hidden" name="username" value="john@example.com" autocomplete="username">')
        ->assertSeeHtml('autocomplete="current-password"');

    $this->assertGuest();
});

test('entering non-existent email transitions to registration step', function () {
    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'newuser@example.com')
        ->call('continueWithEmail')
        ->assertHasNoErrors()
        ->assertSet('step', 'email_register')
        ->assertSee(__('Create Account'))
        ->assertSee(__('Your Name'))
        ->assertSee(__('Password'))
        ->assertSee(__('Create Account & Continue'))
        ->assertSee(__('Change email'))
        ->assertSeeHtml('<input type="hidden" name="username" value="newuser@example.com" autocomplete="username">')
        ->assertSeeHtml('autocomplete="name"')
        ->assertSeeHtml('autocomplete="new-password"');

    $this->assertGuest();
});

test('entering passwordless existing user email transitions to register/set-password step with prefilled name', function () {
    User::factory()->passwordless()->create([
        'name' => 'Existing User',
        'email' => 'googleuser@example.com',
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'googleuser@example.com')
        ->call('continueWithEmail')
        ->assertHasNoErrors()
        ->assertSet('step', 'email_register')
        ->assertSet('name', 'Existing User')
        ->assertSee(__('Create Account'));

    $this->assertGuest();
});

test('user can click change email to return to email entry step', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('secret123'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'john@example.com')
        ->call('continueWithEmail')
        ->assertSet('step', 'email_password')
        ->call('editEmail')
        ->assertSet('step', 'phone')
        ->assertSet('authMode', 'email')
        ->assertSee(__('Continue with Email'))
        ->assertDontSee(__('Password'));
});

test('existing user can login with password on password step', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('my-secret-password'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'john@example.com')
        ->call('continueWithEmail')
        ->assertSet('step', 'email_password')
        ->set('password', 'my-secret-password')
        ->call('loginWithEmail')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

test('user cannot login with wrong password', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('correct-password'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'john@example.com')
        ->call('continueWithEmail')
        ->set('password', 'wrong-password')
        ->call('loginWithEmail')
        ->assertHasErrors(['password'])
        ->assertSee(__('auth.failed'));

    $this->assertGuest();
});

test('new user can register with name and password on registration step', function () {
    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'freshuser@example.com')
        ->call('continueWithEmail')
        ->assertSet('step', 'email_register')
        ->set('name', 'Fresh User')
        ->set('password', 'secret-password-123')
        ->call('registerWithEmail')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticated();
    $user = User::where('email', 'freshuser@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Fresh User')
        ->and($user->wishlists()->count())->toBe(1);
});

test('passwordless user can set password and login on registration step', function () {
    $user = User::factory()->passwordless()->create([
        'name' => 'Google Person',
        'email' => 'google@example.com',
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'google@example.com')
        ->call('continueWithEmail')
        ->assertSet('step', 'email_register')
        ->set('password', 'new-secure-password')
        ->call('registerWithEmail')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->password)->not->toBeNull();
});

test('registration validates name and password constraints', function () {
    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'validate@example.com')
        ->call('continueWithEmail')
        ->set('name', 'A') // min 2
        ->set('password', '123') // min 6
        ->call('registerWithEmail')
        ->assertHasErrors([
            'name' => 'min',
            'password' => 'min',
        ]);

    $this->assertGuest();
});

test('email is trimmed before authentication and registration', function () {
    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', '  trim@example.com  ')
        ->call('continueWithEmail')
        ->set('name', '  Trimmed User  ')
        ->set('password', 'password123')
        ->call('registerWithEmail')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $user = User::where('email', 'trim@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Trimmed User');
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
        ->call('continueWithEmail')
        ->set('password', 'my-secret-password')
        ->call('loginWithEmail')
        ->assertHasNoErrors();

    $this->assertAuthenticated();

    $recallerName = Auth::guard('web')->getRecallerName();
    $queuedCookies = Cookie::queued($recallerName);
    expect($queuedCookies)->not->toBeNull();
});

test('login attempts are throttled after too many failed password attempts', function () {
    User::factory()->create([
        'email' => 'throttle@example.com',
        'password' => Hash::make('correct-pass'),
    ]);

    RateLimiter::clear(RateLimiter::availableIn('throttle'));

    $component = Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'throttle@example.com')
        ->call('continueWithEmail')
        ->set('password', 'wrong-pass');

    for ($i = 0; $i < 5; $i++) {
        $component->call('loginWithEmail')
            ->assertHasErrors(['password']);
    }

    $component->call('loginWithEmail')
        ->assertHasErrors(['password']);
});

test('first login notification is triggered on new user email registration', function () {
    config(['mail.admin_address' => 'admin@example.com']);
    Notification::fake();

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'firsttime@example.com')
        ->call('continueWithEmail')
        ->set('name', 'First Timer')
        ->set('password', 'secret123')
        ->call('registerWithEmail')
        ->assertHasNoErrors();

    $user = User::where('email', 'firsttime@example.com')->first();

    Notification::assertSentOnDemand(
        NewUserFirstLoginNotification::class,
        function ($notification, $channels, $notifiable) use ($user) {
            return $notifiable->routes['mail'] === 'admin@example.com'
                && $notification->user->id === $user->id;
        }
    );

    expect($user->fresh()->last_login_at)->not->toBeNull();
});
