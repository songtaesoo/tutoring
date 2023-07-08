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

        \App\Models\Student::factory(5)->create();
        \App\Models\Tutor::factory(5)->create();
        \App\Models\Course::factory(10)->create();
        \App\Models\Tutoring::factory(10)->create();
    }
}
