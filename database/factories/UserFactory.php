<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+45'.fake()->unique()->numerify('########'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user only has a phone number.
     */
    public function phoneOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user only has an email address.
     */
    public function emailOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => null,
            'phone_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user does not have a password set.
     */
    public function passwordless(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
        ]);
    }

    /**
     * Indicate the legacy ID for the user.
     */
    public function withLegacyId(?int $legacyId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'legacy_id' => $legacyId ?? fake()->unique()->numberBetween(1, 99999),
        ]);
    }
}
