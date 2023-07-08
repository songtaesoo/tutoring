<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AppConfigSeeder::class);
        $this->call(SupportLanguageSeeder::class);
        $this->call(SupportTypeSeeder::class);

        \App\Models\User::factory(10)->create();
        \App\Models\Course::factory(10)->create();
        \App\Models\Certification::factory(10)->create();
        \App\Models\Student::factory(10)->create();
        \App\Models\Tutor::factory(10)->create();
        \App\Models\TutorStatus::factory(10)->create();

        \App\Models\Tutoring::factory(10)->create();
        \App\Models\CourseType::factory(10)->create();
        \App\Models\CourseTicket::factory(10)->create();
        \App\Models\CourseLanguage::factory(10)->create();


    }
}
