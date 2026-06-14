<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\StudentGamificationStat;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating specialized testing accounts...');

        $admin = User::firstOrCreate(
            ['email' => 'admin@iqbrave.com'],
            ['name' => 'Testing Admin', 'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN]
        );

        $assessor = User::firstOrCreate(
            ['email' => 'assessor@iqbrave.com'],
            ['name' => 'Testing Assessor', 'password' => Hash::make('password'), 'role' => User::ROLE_ASSESSOR]
        );

        $student = User::firstOrCreate(
            ['email' => 'student@iqbrave.com'],
            ['name' => 'Testing Student', 'password' => Hash::make('password'), 'role' => User::ROLE_STUDENT]
        );

        $course = Course::first();
        if ($course) {
            Enrollment::firstOrCreate(
                ['user_id' => $student->id, 'course_id' => $course->id],
                ['enrolled_at' => now(), 'status' => 'active']
            );
        }

        StudentGamificationStat::firstOrCreate(
            ['user_id' => $student->id],
            ['total_xp' => 10, 'current_streak' => 1, 'hearts' => 5]
        );

        $this->command->info('Testing Accounts created! (password is "password" for all)');
    }
}
