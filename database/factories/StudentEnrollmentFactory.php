<?php

namespace Database\Factories;

use App\Models\StudentEnrollment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentEnrollmentFactory extends Factory
{
    protected $model = StudentEnrollment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'enrolled_at' => now()->subDays($this->faker->numberBetween(1, 60)),
            'status' => 'active',
        ];
    }
}
