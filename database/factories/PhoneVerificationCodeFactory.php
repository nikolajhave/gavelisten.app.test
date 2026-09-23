<?php

namespace Database\Factories;

use App\Models\PhoneVerificationCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhoneVerificationCode>
 */
class PhoneVerificationCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => '+45'.fake()->numerify('########'),
            'code' => (string) fake()->numerify('######'),
            'expires_at' => now()->addMinutes(10),
        ];
    }

    /**
     * Indicate that the verification code is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(5),
        ]);
    }
}
