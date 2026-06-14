<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Course;
use App\Models\MicroTopic;

class SystemWipeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Wiping old fake data...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Delete all courses and inherently their related modules/units via cascade if applicable,
        // but let's truncate to be clean.
        DB::table('courses')->truncate();
        DB::table('modules')->truncate();
        DB::table('units')->truncate();
        DB::table('lessons')->truncate();
        DB::table('quizzes')->truncate();
        DB::table('quiz_questions')->truncate();
        DB::table('quiz_options')->truncate();
        DB::table('micro_topics')->truncate();
        
        // Remove dummy students but keep the Admin and Instructors
        User::where('role', User::ROLE_STUDENT)->delete();
        
        DB::table('enrollments')->truncate();
        DB::table('lesson_progress')->truncate();
        DB::table('student_gamification_stats')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('System cleansed. Ready for real NVQ Data.');
    }
}
