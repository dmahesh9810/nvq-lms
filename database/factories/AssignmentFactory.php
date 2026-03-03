<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'unit_id' => \App\Models\Unit::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];
    }
}
