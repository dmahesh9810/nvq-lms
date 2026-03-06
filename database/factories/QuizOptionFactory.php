<?php

namespace Database\Factories;

use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizOptionFactory extends Factory
{
    protected $model = QuizOption::class;

    public function definition(): array
    {
        return [
            'question_id' => QuizQuestion::factory(),
            'option_text' => $this->faker->sentence(4),
            'is_correct' => false, // Default to false, seeder will define correct ones
        ];
    }
}
