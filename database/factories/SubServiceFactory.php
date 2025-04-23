<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubService>
 */
class SubServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     

     
    public function definition(): array
    {

        $title = ['driver' ,'fast','Fleet Mode'];
        $image = [
        'https://www.intelivita.com/wp-content/uploads/2022/07/How-to-Create-a-Ride-Hailing-App-like-Uber-1024x542.webp' ,
        'https://images.prismic.io/intuzwebsite/b3a0427b-dc3b-4d68-af6d-02ee15d9e2f3_Main.png?auto=compress,format',
        'https://i.ytimg.com/vi/pebqOtSO2yI/maxresdefault.jpg'
       ];

        return [
            'name' => $title [$this->faker->numberBetween(0,2)],
            'image'=> $image [$this->faker->numberBetween(0,2)],
            'status' =>true,
            'description' => $this->faker->text(50),
            'openPrice' => 1000,
            'kmPrice' => 200,
            'minutePrice' => 200,
            'serviceId' =>Service::query()->inRandomOrder()->first()?->id ??Service::factory(),
        ];
    }
}
