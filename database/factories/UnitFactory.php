<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'module_id' => \App\Models\Module::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
