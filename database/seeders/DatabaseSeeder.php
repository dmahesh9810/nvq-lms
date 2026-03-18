<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class ,
            AssessorSeeder::class ,
            CourseSeeder::class ,
            ModuleSeeder::class ,
            UnitSeeder::class ,
            LessonSeeder::class ,
            QuizSeeder::class ,
            AssignmentSeeder::class ,
            StudentDataSeeder::class ,
        ]);
    }
}
