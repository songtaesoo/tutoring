<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\AppConfig;

use Illuminate\Support\Str;

class CertificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'config_id' => AppConfig::where('value', 'aos_device_receive_token')->first()->id,
            'value' => Str::random(200)
        ];
    }
}
