<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // We use existing units here so that the quizzes belong to the course 
        // the seeded students are actually enrolled in.
        $units = Unit::take(3)->get();

        if ($units->isEmpty()) {
            Quiz::factory()->count(3)->create();
        }
        else {
            foreach ($units as $unit) {
                Quiz::factory()->create([
                    'unit_id' => $unit->id
                ]);
            }
        }
    }
}
