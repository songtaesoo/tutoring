<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupportLanguage;
use App\Models\Course;

class CourseLanguageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => Course::inRandomOrder()->first()->id,,
            'language_id' => SupportLanguage::inRandomOrder()->first()->id,
            'type' => '',
            'description' => '',
        ];
    }
}
