<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use App\Models\Student;

use Carbon\Carbon;

class CourseTicketFactory extends Factory
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
            'ticket_no' => getRandomValue('string', 10),
            'started_at' => Carbon::now(),
            'ended_at' => Carbon::now()->addMonths(1)
        ];
    }
}
