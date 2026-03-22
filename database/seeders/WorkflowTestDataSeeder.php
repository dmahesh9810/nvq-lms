<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentResult;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkflowTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $instructor = User::updateOrCreate(
            ['email' => 'instructor@workflow.com'],
            [
                'name' => 'John Instructor',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'email_verified_at' => now(),
            ]
        );

        $assessor = User::updateOrCreate(
            ['email' => 'assessor@workflow.com'],
            [
                'name' => 'Sarah Assessor',
                'password' => Hash::make('password'),
                'role' => 'assessor',
                'email_verified_at' => now(),
            ]
        );

        $student = User::updateOrCreate(
            ['email' => 'student@workflow.com'],
            [
                'name' => 'Mark Student',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Course Structure
        $course = Course::firstOrCreate(
            ['title' => 'NVQ Level 4 - Software Development'],
            [
                'description' => 'Comprehensive software engineering course.',
                'instructor_id' => $instructor->id,
                'status' => 'published',
            ]
        );

        $module = Module::firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Web Application Development'],
            ['order' => 1]
        );

        $unit = Unit::firstOrCreate(
            ['module_id' => $module->id, 'title' => 'Laravel Framework Essentials'],
            ['order' => 1]
        );

        $assignment = Assignment::firstOrCreate(
            ['unit_id' => $unit->id, 'title' => 'Build a CRUD System'],
            [
                'description' => 'Build a simple CRUD system using Laravel.',
                'is_active' => true,
                'max_marks' => 100,
            ]
        );

        // 3. Create Submissions in different stages

        // STAGE 1: Submitted (Waiting for Instructor)
        AssignmentSubmission::firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'status' => 'submitted'
            ],
            [
                'file_path' => 'submissions/test_file.pdf',
                'submitted_at' => now()->subDays(2),
            ]
        );

        // STAGE 2: Reviewed (Waiting for Assessor) - THIS ONE SHOULD APPEAR IN ASSESSOR DASHBOARD
        $student2 = User::factory()->create(['role' => 'student', 'name' => 'Alice Reviewed']);
        AssignmentSubmission::firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $student2->id,
            ],
            [
                'file_path' => 'submissions/reviewed_file.pdf',
                'submitted_at' => now()->subDays(3),
                'status' => 'reviewed',
                'instructor_id' => $instructor->id,
                'instructor_review' => 'This submission is complete and well-structured. Ready for final competency assessment.',
                'instructor_reviewed_at' => now()->subMinutes(30),
            ]
        );

        // STAGE 3: Assessed (Finalized)
        $student3 = User::factory()->create(['role' => 'student', 'name' => 'Bob Assessed']);
        $sub3 = AssignmentSubmission::firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $student3->id,
            ],
            [
                'file_path' => 'submissions/assessed_file.pdf',
                'submitted_at' => now()->subDays(5),
                'status' => 'assessed',
                'instructor_id' => $instructor->id,
                'instructor_review' => 'Excellent work.',
                'instructor_reviewed_at' => now()->subDays(1),
            ]
        );

        AssignmentResult::firstOrCreate(
            ['submission_id' => $sub3->id],
            [
                'assessor_id' => $assessor->id,
                'competency_status' => 'competent',
                'marks' => 95,
                'feedback' => 'Outstanding implementation of Laravel patterns.',
                'graded_at' => now(),
            ]
        );

        echo "Seeding successful!\n";
        echo "Instructor: instructor@workflow.com / password\n";
        echo "Assessor: assessor@workflow.com / password\n";
        echo "Student: student@workflow.com / password\n";
    }
}
