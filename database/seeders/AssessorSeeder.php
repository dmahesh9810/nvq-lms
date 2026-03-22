<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AssessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'assessor@example.com'],
            [
                'name' => 'Assessor User',
                'password' => Hash::make('password'),
                'role' => 'assessor',
                'email_verified_at' => now(),
            ]
        );
    }
}
