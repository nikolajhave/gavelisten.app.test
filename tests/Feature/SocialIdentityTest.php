<?php

use App\Models\SocialIdentity;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('social identity belongs to user', function () {
    $user = User::factory()->create();
    $identity = SocialIdentity::factory()->create([
        'user_id' => $user->id,
        'provider_name' => 'google',
        'provider_id' => '1234567890',
    ]);

    expect($identity->user->id)->toBe($user->id)
        ->and($identity->provider_name)->toBe('google')
        ->and($identity->provider_id)->toBe('1234567890');
});

test('social identity enforces unique provider_name and provider_id combination', function () {
    SocialIdentity::factory()->create([
        'provider_name' => 'google',
        'provider_id' => 'duplicate-id-123',
    ]);

    expect(fn () => SocialIdentity::factory()->create([
        'provider_name' => 'google',
        'provider_id' => 'duplicate-id-123',
    ]))->toThrow(QueryException::class);
});

test('social identity is deleted when parent user is deleted', function () {
    $user = User::factory()->create();
    $identity = SocialIdentity::factory()->create(['user_id' => $user->id]);

    $user->delete();

    $this->assertDatabaseMissing('social_identities', [
        'id' => $identity->id,
    ]);
});
