<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Assignment;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = Unit::take(3)->get();

        foreach ($units as $unit) {
            Assignment::factory()->create([
                'unit_id' => $unit->id,
                'title' => 'Practical Task: ' . $unit->title,
            ]);
        }
    }
}
