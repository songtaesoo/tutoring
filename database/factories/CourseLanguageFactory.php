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
            'language_id' => SupportLanguage::inRandomOrder()->first()->id,
            'description' => ''
        ];
    }
}
