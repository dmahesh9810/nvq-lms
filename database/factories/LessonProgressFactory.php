<?php

namespace Database\Factories;

use App\Models\LessonProgress;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonProgressFactory extends Factory
{
    protected $model = LessonProgress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_id' => Lesson::factory(),
            'completed_at' => now()->subHours($this->faker->numberBetween(1, 48)),
        ];
    }
}
