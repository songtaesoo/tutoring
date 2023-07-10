<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\SupportLanguage;
use App\Models\SupportType;
use App\Models\Tutor;
use App\Models\TutorStatus;

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
            'user_id' => function () {
                return User::factory()->create(['role' => 'tutor'])->id;
            },
            'language_id' => SupportLanguage::inRandomOrder()->first()->id,
            'type_id' => SupportType::inRandomOrder()->first()->id,
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'status' => $this->faker->randomElement(['active', 'deactive', 'inClass']),
            'country' => $this->faker->randomElement(['en', 'ca', 'cn', 'kr', 'jp', 'vn']),
            'country_type' => $this->faker->randomElement(['global', 'native'])
        ];
    }
}
