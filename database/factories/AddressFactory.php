<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Addresss>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'address' => $this->faker->address(),
            'addressName' => $this->faker->name(),
            'town' => $this->faker->city(),
            'phone' => $this->faker->numerify('09########'),
            'description' =>  $this->faker->text(50),
            'lat' => $this->faker->latitude(32.0, 37.5),
            'lang' =>  $this->faker->longitude(35.5, 42.0),
            'userId'=>User::query()->inRandomOrder()->first()?->id ??User::factory(),
        ];
    }
}
