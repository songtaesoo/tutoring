<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Models\Course;
use App\Models\CourseType;
use App\Models\CourseTicket;
use App\Models\CourseLanguage;

class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['입문용 1대1 회화 연습', 'ㄱㅎ급용 1대1 회화 연습']),
            'period' => $this->faker->randomElement([3, 6]),
            'time' => $this->faker->randomElement([10, 15]),
            'count' => $this->faker->randomElement([30, 90]),
            'is_sale' => true,
            'sale_started_at' => Carbon::now(),
            'sale_ended_at' => Carbon::now()->addMonths(12)
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Course $course) {
            $course->type()->save(CourseType::factory()->make());
            $course->tickets()->save(CourseTicket::factory()->make());
            $course->language()->save(CourseLanguage::factory()->make());
        });
    }
}
