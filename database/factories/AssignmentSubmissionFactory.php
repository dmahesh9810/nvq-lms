<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_id' => \App\Models\Assignment::factory(),
            'user_id' => \App\Models\User::factory(),
            'file_path' => 'submissions/' . fake()->uuid() . '.pdf',
            'status' => 'submitted',
            'submitted_at' => now(),
        ];
    }
}
