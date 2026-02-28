<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Creates one user for each role for development/testing.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@lms.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Instructor User',
                'email' => 'instructor@lms.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_INSTRUCTOR,
            ],
            [
                'name' => 'Assessor User',
                'email' => 'assessor@lms.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ASSESSOR,
            ],
            [
                'name' => 'Student User',
                'email' => 'student@lms.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STUDENT,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
            ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✅ Seeded 4 users: admin, instructor, assessor, student (password: password)');
    }
}
