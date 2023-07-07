<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'course_id' => Course::inRandomOrder()->first()->id,
            'student_id' => Student::inRandomOrder()->first()->id,
            'ticket_no' => getRandomValue('string', 10),
            'name' => Course::inRandomOrder()->first()->name,
            'price' => 725000,
            'is_sale' => true,
            'started_at' => Carbon::now(),
            'ended_at' => Carbon::now()->addMonths(1),
        ];
    }
}
