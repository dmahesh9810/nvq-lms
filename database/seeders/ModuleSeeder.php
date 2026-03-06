<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::where('slug', 'computer-hardware-technician-nvq-level-4')->firstOrFail();

        $modules = [
            'Fundamentals of electricity and electronics',
            'Computer fundamentals',
            'Functions of Operating Systems, Utility Systems and Application Software',
            'Components of the computer system',
            'Entrepreneurship',
            'Green IT Concept',
            'Preparation of estimate/quotation',
            'Fundamentals of cyber security',
            'Troubleshooting and Repairing computer system',
            'Assemble Computers',
            'Maintain computers and peripherals',
            'Install and configure computer software',
            'Setup and Maintain Network systems',
            'Communication skills for workplace',
            'Workshop calculation & Science',
            'Team work',
            'Occupational Safety & Health and Environmental Aspects',
        ];

        foreach ($modules as $index => $moduleTitle) {
            Module::firstOrCreate(
            [
                'course_id' => $course->id,
                'title' => $moduleTitle,
            ],
            [
                'description' => 'Comprehensive module covering ' . $moduleTitle . ' aligning with NVQ Level 4 standards.',
                'order' => $index + 1,
                'is_active' => true,
            ]
            );
        }
    }
}
