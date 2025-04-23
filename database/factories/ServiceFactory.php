<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ['fleet Taxi' ,'Yellow Taxi','Fleet Mode'];
        $image = 
        [
        'https://img.freepik.com/vecteurs-libre/taxi-icon-taxi-service-yellow-taxi-car-gray-background_1057-4799.jpg' ,
        'https://previews.123rf.com/images/metelsky/metelsky1801/metelsky180100041/92779675-ic%C3%B4ne-de-voiture-de-taxi-ou-signe-isol%C3%A9-sur-fond-blanc-conception-du-service-de-taxi-illustration.jpg',
        'https://img.freepik.com/vecteurs-premium/logo-service-voiture-quatre-roues-plus-tres-elegant_639175-1452.jpg?w=740'
    ];


        return [
            'image' =>$title [$this->faker->numberBetween(0,2)],
            'title' => $image [$this->faker->numberBetween(0,2)],
            'status'=> true ,
        ];
    }
}
