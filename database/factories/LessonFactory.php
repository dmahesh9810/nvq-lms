<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(3, true),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', // Mock URL
            'order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
