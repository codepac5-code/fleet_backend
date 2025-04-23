<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'officeName' => $this->faker->firstName(),
            // 'inQueue' => $this->faker->randomElement([true, false]),
            // 'limitOrders' => 10,
            // 'logo' => $this->faker->imageUrl(640, 640, 'people'),
            // 'type' => 0,
            // 'limitMoney' => 200,
            // 'isDeleted' => false,
            // 'hasTravelMode' => false,
            // 'openPrice' => 100,
            // 'isEnabled' => true ,
            // 'phone1' => $this->faker->numerify('09########'),
            // 'phone2' => $this->faker->numerify('09########'),
            // 'phone3' => $this->faker->numerify('09########'),
            // 'perMin' => 200,
            // 'address' => $this->faker->address(),
            // 'user_id'=>User::query()->inRandomOrder()->first()?->id ??User::factory(),
            // // 'earnings' => 0,
            // 'kmPrice' => 0,
            // 'commissionValue' => 0
            'password' =>  Hash::make('Aa123456**'),
            'email' => $this->faker->unique()->email(),

        ];
    }
}
