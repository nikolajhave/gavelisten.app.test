<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user making a request updates last_login_at when null', function () {
    $user = User::factory()->create([
        'last_login_at' => null,
    ]);

    $this->actingAs($user)->get('/wishlist')->assertOk();

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

test('authenticated user making a request updates last_login_at when older than 1 minute', function () {
    $pastTime = now()->subMinutes(10);
    $user = User::factory()->create([
        'last_login_at' => $pastTime,
    ]);

    $this->actingAs($user)->get('/wishlist')->assertOk();

    $freshUser = $user->fresh();
    expect($freshUser->last_login_at->timestamp)->toBeGreaterThan($pastTime->timestamp);
});

test('authenticated user making multiple requests within 1 minute throttles timestamp updates', function () {
    $recentTime = now()->subSeconds(20);
    $user = User::factory()->create([
        'last_login_at' => $recentTime,
    ]);

    $this->actingAs($user)->get('/wishlist')->assertOk();

    $freshUser = $user->fresh();
    expect($freshUser->last_login_at->timestamp)->toBe($recentTime->timestamp);
});

test('guest request does not fail and touches no user timestamp', function () {
    $user = User::factory()->create([
        'last_login_at' => null,
    ]);

    $this->get('/login')->assertOk();

    expect($user->fresh()->last_login_at)->toBeNull();
});

test('touching user last online preserves user updated_at timestamp', function () {
    $fixedUpdatedAt = now()->subDays(5);

    $user = User::factory()->create([
        'last_login_at' => now()->subHours(2),
        'updated_at' => $fixedUpdatedAt,
    ]);

    $this->actingAs($user)->get('/wishlist')->assertOk();

    $freshUser = $user->fresh();
    expect($freshUser->last_login_at->isCurrentMinute())->toBeTrue()
        ->and($freshUser->updated_at->timestamp)->toBe($fixedUpdatedAt->timestamp);
});
