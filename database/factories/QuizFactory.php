<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Unit;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'title' => $this->faker->sentence(3) . ' Assessment',
            'description' => $this->faker->paragraph(),
            'pass_mark' => 50, // 50% pass mark
            'is_active' => true,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($quiz) {

            $questions = QuizQuestion::factory()
                ->count(5)
                ->create([
                'quiz_id' => $quiz->id
            ]);

            foreach ($questions as $question) {
                // Ensure at least 1 correct option and 3 incorrect options (4 total)
                QuizOption::factory()
                    ->count(3)
                    ->create([
                    'question_id' => $question->id,
                    'is_correct' => false
                ]);

                QuizOption::factory()
                    ->create([
                    'question_id' => $question->id,
                    'is_correct' => true
                ]);
            }

        });
    }
}
