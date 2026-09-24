<?php

namespace Database\Factories;

use App\Models\SocialIdentity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialIdentity>
 */
class SocialIdentityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_name' => fake()->randomElement(['google', 'apple', 'facebook', 'github']),
            'provider_id' => (string) fake()->unique()->numerify('##########'),
        ];
    }

    /**
     * Set the social provider to Google.
     */
    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_name' => 'google',
        ]);
    }

    /**
     * Set the social provider to Apple.
     */
    public function apple(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_name' => 'apple',
        ]);
    }
}
