<?php

namespace Database\Factories;

use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizAttemptFactory extends Factory
{
    protected $model = QuizAttempt::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'user_id' => User::factory(),
            'score' => $this->faker->randomFloat(2, 0, 100),
            'result' => $this->faker->randomElement(['PASS', 'FAIL']),
            'started_at' => now()->subMinutes(30),
            'completed_at' => now(),
        ];
    }
}
