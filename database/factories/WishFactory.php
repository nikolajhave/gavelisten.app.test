<?php

namespace Database\Factories;

use App\Models\Wish;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wish>
 */
class WishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wishlist_id' => Wishlist::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'url' => fake()->url(),
            'price' => fake()->randomFloat(2, 10, 500),
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate the legacy ID for the wish.
     */
    public function withLegacyId(?int $legacyId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'legacy_id' => $legacyId ?? fake()->unique()->numberBetween(1, 99999),
        ]);
    }
}
