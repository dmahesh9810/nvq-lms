<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'submission_id' => \App\Models\AssignmentSubmission::factory(),
            'assessor_id' => \App\Models\User::factory(),
            'marks' => fake()->numberBetween(50, 100),
            'competency_status' => 'competent',
            'feedback' => fake()->sentence(),
            'graded_at' => now(),
        ];
    }
}
