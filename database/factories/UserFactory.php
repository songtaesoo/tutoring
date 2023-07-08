<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Student;
use App\Models\Certification;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role' => $this->faker->randomElement(['tutor', 'student']),
            'status' => $this->faker->randomElement(['active']),
            'remember_token' => Str::random(10)
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            if($user->role == 'tutor'){
                $user->tutor()->save(Tutor::factory()->make());
            }else if($user->role == 'student'){
                $user->student()->save(Student::factory()->make());
            }

            $user->certification()->save(Certification::factory()->make());
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
