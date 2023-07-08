<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use App\Models\Student;
use App\Models\Tutor;

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
        $status = $this->faker->randomElement(['pending', 'processing', 'completed', 'disconnected', 'cancelled', 'reserved']);

        switch ($status){
            case "pending":
                $started_at = null;
                $ended_at = null;
            case "processing":
                $started_at = $this->faker->dateTimeBetween('-1 hour', 'now');
                $ended_at = null;
            case "completed":
            case "disconnected":
            case "cancelled":
            case "reserved":
                $started_at = $this->faker->dateTimeBetween('-2 hour', 'now');
                $ended_at = $this->faker->dateTimeBetween('-1 hour', 'now');
                break;
        }

        return [
            'student_id' => Student::inRandomOrder()->first()->id,
            'tutor_id' => Tutor::inRandomOrder()->first()->id,
            'course_id' => Course::inRandomOrder()->first()->id,
            'status' => $status,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'description' => ''
        ];
    }
}
