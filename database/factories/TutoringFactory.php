<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use App\Models\Student;
use App\Models\Tutor;

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
            'course_id' => Course::inRandomOrder()->first()->id,
            'status' => randomElement(['pending', 'processing', 'completed', 'disconnected', 'cancelled']),
            'started_at' => Carbon::now(),
            'ended_at' => Carbon::now()->addMonths(3),
            'description' => ''
        ];
    }
}
