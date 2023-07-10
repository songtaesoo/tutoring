<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tutoring;

class TutoringCalculationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tutoring_id' => Tutoring::inRandomOrder()->first()->id,
            'amount' => $this->faker->randomElement([5000, 1000, 1500, 2000, 5000]),
            'is_payment' => false,
            'payment_at' => null,
            'description' => ''
        ];
    }
}
