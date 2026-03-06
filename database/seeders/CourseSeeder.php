<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // Target NVQ Course
        Course::firstOrCreate(
        ['slug' => 'computer-hardware-technician-nvq-level-4'],
        [
            'instructor_id' => $instructor->id ?? 1,
            'title' => 'Computer Hardware Technician NVQ Level 4',
            'description' => 'Comprehensive training program mapping directly to the National Vocational Qualification (NVQ) Level 4 curriculum for Computer Hardware Technicians. This course covers everything from fundamental electronics and PC assembly up through deep operating system configuration, networking, troubleshooting, and workplace safety.',
            'status' => 'published',
        ]
        );
    }
}
