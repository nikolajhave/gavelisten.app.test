<?php

use App\Actions\Auth\SendPhoneVerificationCode;
use App\Actions\Auth\VerifyPhoneVerificationCode;
use App\Contracts\SmsService;
use App\Models\PhoneVerificationCode;
use App\Models\User;
use App\Services\LogSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('sms service is bound to log sms service in container', function () {
    $service = app(SmsService::class);

    expect($service)->toBeInstanceOf(LogSmsService::class);
});

test('log sms service logs the generated otp', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('SMS OTP for +4512345678: 123456');

    $service = new LogSmsService;
    $result = $service->sendOtp('+4512345678', '123456');

    expect($result)->toBeTrue();
});

test('send phone verification code action generates 6 digit code valid for 10 minutes and dispatches via sms', function () {
    $mockSms = Mockery::mock(SmsService::class);
    $mockSms->shouldReceive('sendOtp')
        ->once()
        ->with('+4512345678', Mockery::pattern('/^[0-9]{6}$/'))
        ->andReturnTrue();

    $action = new SendPhoneVerificationCode($mockSms);
    $record = $action->execute('+45 12 34 56 78');

    expect($record->phone)->toBe('+4512345678')
        ->and($record->code)->toMatch('/^[0-9]{6}$/')
        ->and($record->expires_at->gt(now()->addMinutes(9)))->toBeTrue()
        ->and($record->expires_at->lte(now()->addMinutes(10)))->toBeTrue();

    $this->assertDatabaseHas('phone_verification_codes', [
        'id' => $record->id,
        'phone' => '+4512345678',
        'code' => $record->code,
    ]);
});

test('send phone verification code deletes old codes for the same phone number', function () {
    PhoneVerificationCode::factory()->create(['phone' => '+4512345678', 'code' => '111111']);
    PhoneVerificationCode::factory()->create(['phone' => '+4512345678', 'code' => '222222']);

    $mockSms = Mockery::mock(SmsService::class);
    $mockSms->shouldReceive('sendOtp')->once()->andReturnTrue();

    $action = new SendPhoneVerificationCode($mockSms);
    $newRecord = $action->execute('+4512345678');

    expect(PhoneVerificationCode::where('phone', '+4512345678')->count())->toBe(1)
        ->and(PhoneVerificationCode::where('phone', '+4512345678')->first()->id)->toBe($newRecord->id);
});

test('verify phone verification code creates a new verified user and auto-provisions wishlist', function () {
    PhoneVerificationCode::factory()->create([
        'phone' => '+4599887766',
        'code' => '654321',
        'expires_at' => now()->addMinutes(10),
    ]);

    $action = new VerifyPhoneVerificationCode;
    $user = $action->execute('+45 99 88 77 66', '654321');

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->phone)->toBe('+4599887766')
        ->and($user->phone_verified_at)->not->toBeNull()
        ->and($user->wishlists)->toHaveCount(1)
        ->and($user->wishlists->first()->title)->toBe(__('My Wishlist'));

    $this->assertDatabaseMissing('phone_verification_codes', [
        'phone' => '+4599887766',
    ]);
});

test('verify phone verification code finds existing user and verifies phone', function () {
    $existing = User::factory()->phoneOnly()->unverified()->create([
        'phone' => '+4599887766',
    ]);

    PhoneVerificationCode::factory()->create([
        'phone' => '+4599887766',
        'code' => '654321',
        'expires_at' => now()->addMinutes(10),
    ]);

    $action = new VerifyPhoneVerificationCode;
    $user = $action->execute('+4599887766', '654321');

    expect($user->id)->toBe($existing->id)
        ->and($user->fresh()->phone_verified_at)->not->toBeNull();
});

test('verify phone verification code throws exception for invalid code', function () {
    PhoneVerificationCode::factory()->create([
        'phone' => '+4599887766',
        'code' => '654321',
        'expires_at' => now()->addMinutes(10),
    ]);

    $action = new VerifyPhoneVerificationCode;

    expect(fn () => $action->execute('+4599887766', '000000'))
        ->toThrow(ValidationException::class);
});

test('verify phone verification code throws exception for expired code', function () {
    PhoneVerificationCode::factory()->expired()->create([
        'phone' => '+4599887766',
        'code' => '654321',
    ]);

    $action = new VerifyPhoneVerificationCode;

    expect(fn () => $action->execute('+4599887766', '654321'))
        ->toThrow(ValidationException::class);
});
