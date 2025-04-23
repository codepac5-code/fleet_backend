<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
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
            'officeId' => Office::query()->inRandomOrder()->first()?->id ??Office::factory(),
            'address' => $this->faker->address(),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'state' => $this->faker->city(),
        ];
    }
}
