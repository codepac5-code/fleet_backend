<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstName' => $this->faker->firstName(),
            'lastName' =>  $this->faker->lastName(),
            'phoneNumber' => $this->faker->numerify('09########'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'is_registered' => false,
            'photo' => $this->faker->imageUrl(640, 640, 'people'),
            'walletBalance' => 0,
            'email_verified_at' => now(),
            'password' =>  Hash::make('Aa123456**'),
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
        ]);
    }
}
