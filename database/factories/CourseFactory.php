<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Models\Course;
use App\Models\CourseTicket;
use App\Models\CoursePayment;
use App\Models\SupportLanguage;
use App\Models\SupportType;

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
            'type_id' => SupportType::inRandomOrder()->first()->id,
            'language_id' => SupportLanguage::inRandomOrder()->first()->id,
            'name' => $this->faker->randomElement(['입문자용 1대1 회화 연습', '중급자용 1대1 회화 연습']),
            'period' => $this->faker->randomElement([3, 6]),
            'time' => $this->faker->randomElement([10, 15]),
            'count' => $this->faker->randomElement([30, 90]),
            'price' => $this->faker->randomElement([30000, 90000, 12000, 120000, 354000]),
            'sort' => $this->faker->unique()->randomDigit,
            'is_sale' => true,
            'sale_started_at' => Carbon::now(),
            'sale_ended_at' => Carbon::now()->addMonths(12)
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Course $course) {
            CourseTicket::factory()->create(['course_id' => $course['id']]);
            CoursePayment::factory()->create(['course_id' => $course['id']]);
        });
    }
}
