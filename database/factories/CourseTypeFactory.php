<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupportType;
use App\Models\Course;

class CourseTypeFactory extends Factory
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
            'description' => ''
        ];
    }
}
