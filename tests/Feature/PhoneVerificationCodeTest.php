<?php

use App\Models\PhoneVerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('phone verification code can be created with phone, code, and expires_at', function () {
    $code = PhoneVerificationCode::factory()->create([
        'phone' => '+45 12 34 56 78',
        'code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    expect($code->phone)->toBe('+4512345678')
        ->and($code->code)->toBe('123456')
        ->and($code->expires_at)->toBeInstanceOf(Carbon::class)
        ->and($code->isValid())->toBeTrue()
        ->and($code->isExpired())->toBeFalse();
});

test('phone verification code active scope filters out expired codes', function () {
    $activeCode = PhoneVerificationCode::factory()->create([
        'phone' => '+4511111111',
        'expires_at' => now()->addMinutes(5),
    ]);

    $expiredCode = PhoneVerificationCode::factory()->expired()->create([
        'phone' => '+4522222222',
    ]);

    $activeCodes = PhoneVerificationCode::active()->get();

    expect($activeCodes->pluck('id')->all())->toContain($activeCode->id)
        ->and($activeCodes->pluck('id')->all())->not->toContain($expiredCode->id)
        ->and($expiredCode->isValid())->toBeFalse()
        ->and($expiredCode->isExpired())->toBeTrue();
});
