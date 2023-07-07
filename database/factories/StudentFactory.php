<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Faker\Factory as Faker;

class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = Faker::create('ko_KR');

        return [
            'user_id' => User::inRandomOrder()->where('role', 'student')->first()->id,
            'name' => $faker->name,
            'phone' => '010-'.strval($faker->randomNumber(4)).'-'.strval($faker->randomNumber(4))
        ];
    }
}
