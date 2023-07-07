<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->where('role', 'student')->first()->id,
            'name' => $this->faker->name,
            'phone' => $this->faker->cellPhoneNumber
        ];
    }
}
