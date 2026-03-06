<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1 Admin
        User::firstOrCreate(
        ['email' => 'admin@iqbrave.com'],
        [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]
        );

        // 2 Instructors
        User::firstOrCreate(
        ['email' => 'instructor1@iqbrave.com'],
        [
            'name' => 'John Instructor',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]
        );

        User::firstOrCreate(
        ['email' => 'instructor2@iqbrave.com'],
        [
            'name' => 'Jane Trainer',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]
        );

        // 50 Students (Randomly Generated using the Factory)
        User::factory()->count(50)->create([
            'role' => 'student',
            'password' => Hash::make('password'),
        ]);
    }
}
