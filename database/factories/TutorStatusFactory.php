<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tutor;

class TutorStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tutor_id' => Tutor::inRandomOrder()->first()->id,
            'status' => $this->faker->randomElement(['active', 'deactive', 'inClass'])
        ];
    }
}
