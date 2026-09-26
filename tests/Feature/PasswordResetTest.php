<?php

use App\Livewire\Auth\PhoneAuth;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('password step displays forgot password button', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'user@example.com')
        ->call('continueWithEmail')
        ->assertSet('step', 'email_password')
        ->assertSee(__('Forgot password?'));
});

test('user can request password reset link from password step', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
    ]);

    Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'user@example.com')
        ->call('continueWithEmail')
        ->assertSet('step', 'email_password')
        ->call('sendPasswordResetLink')
        ->assertHasNoErrors()
        ->assertSee(trans('passwords.sent'));

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('password reset notification contains valid reset URL', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
    ]);

    $token = Password::broker()->createToken($user);
    $notification = new ResetPasswordNotification($token);
    $mail = $notification->toMail($user);

    expect($mail->actionUrl)->toBe(route('password.reset', [
        'token' => $token,
        'email' => 'user@example.com',
    ]));
});

test('password reset link request is throttled on rapid repeated attempts', function () {
    Notification::fake();

    User::factory()->create([
        'email' => 'ratelimit@example.com',
        'password' => Hash::make('password123'),
    ]);

    $component = Livewire::test(PhoneAuth::class)
        ->set('authMode', 'email')
        ->set('email', 'ratelimit@example.com')
        ->call('continueWithEmail');

    $component->call('sendPasswordResetLink')
        ->assertHasNoErrors()
        ->assertSee(trans('passwords.sent'));

    $component->call('sendPasswordResetLink')
        ->assertHasErrors(['password']);
});

test('reset password page is accessible with token', function () {
    $response = $this->get('/reset-password/sample-token?email=user@example.com');

    $response->assertStatus(200)
        ->assertSeeLivewire(ResetPassword::class)
        ->assertSee(__('Reset Password'))
        ->assertSee(__('New Password'));
});

test('user can reset password with valid token and is automatically logged in', function () {
    $user = User::factory()->create([
        'email' => 'resetuser@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::broker()->createToken($user);

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', 'resetuser@example.com')
        ->set('password', 'brand-new-password')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    expect(Hash::check('brand-new-password', $user->fresh()->password))->toBeTrue();
});

test('user cannot reset password with invalid token', function () {
    User::factory()->create([
        'email' => 'invalidtoken@example.com',
        'password' => Hash::make('old-password'),
    ]);

    Livewire::test(ResetPassword::class, ['token' => 'invalid-token-123'])
        ->set('email', 'invalidtoken@example.com')
        ->set('password', 'new-password-123')
        ->call('resetPassword')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

test('reset password validates password minimum length', function () {
    User::factory()->create([
        'email' => 'shortpass@example.com',
        'password' => Hash::make('old-password'),
    ]);

    Livewire::test(ResetPassword::class, ['token' => 'some-token'])
        ->set('email', 'shortpass@example.com')
        ->set('password', '123')
        ->call('resetPassword')
        ->assertHasErrors(['password' => 'min']);

    $this->assertGuest();
});
