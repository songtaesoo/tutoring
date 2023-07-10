<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TutoringCalculation;
use App\Models\CourseTicket;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\Tutoring;

use Carbon\Carbon;
class TutoringFactory extends Factory
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
            'tutor_id' => Tutor::inRandomOrder()->first()->id,
            'ticket_id' => CourseTicket::inRandomOrder()->first()->id,
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'disconnected', 'cancelled', 'reserved']),
            'started_at' => $this->faker->dateTimeBetween('-2 hour', 'now'),
            'ended_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'description' => ''
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Tutoring $tutoring) {
            TutoringCalculation::factory()->create(['tutoring_id' => $tutoring['id']]);
        });
    }
}
