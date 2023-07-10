<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;

class CoursePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_id' => Student::inRandomOrder()->first()->id,
            'origin_payment_id' => null,
            'payment_no' => getRandomValue('number', 20),
            'amount' => $this->faker->randomElement([30000, 40000, 320000, 180000, 1231000]),
            'provider' => $this->faker->randomElement(['card', 'account', 'easy']),
            'auth_no' => $this->faker->unique()->numerify('######'),
            'payment_at' => $this->faker->dateTimeBetween('-2 hour', 'now')
        ];
    }
}
