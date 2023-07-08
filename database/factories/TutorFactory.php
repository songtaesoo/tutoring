<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\SupportLanguage;
use App\Models\SupportType;

class TutorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->where('role', 'tutor')->first()->id,
            'language_id' => SupportLanguage::inRandomOrder()->first()->id,
            'type_id' => SupportType::inRandomOrder()->first()->id,
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'country' => $this->faker->randomElement(['en, ca, cn, kr, jp, vn']),
            'type' => $this->faker->randomElement(['Global', 'Native']),
            'description' => ''
        ];
    }
}
