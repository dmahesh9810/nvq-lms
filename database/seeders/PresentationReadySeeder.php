<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\StudentGamificationStat;
use App\Models\Enrollment;
use Illuminate\Support\Str;

class PresentationReadySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Presentation Ready Data Seeder...');

        // 1. Create Realistic Users
        $this->command->info('Creating Users...');
        
        $admin = User::firstOrCreate(
            ['email' => 'admin@iqbrave.com'],
            ['name' => 'Sachi SystemAdmin', 'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN]
        );

        $instructor = User::firstOrCreate(
            ['email' => 'instructor@iqbrave.com'],
            ['name' => 'Kamal Perera', 'password' => Hash::make('password'), 'role' => User::ROLE_INSTRUCTOR]
        );

        $assessor = User::firstOrCreate(
            ['email' => 'assessor@iqbrave.com'],
            ['name' => 'Sunil Shantha', 'password' => Hash::make('password'), 'role' => User::ROLE_ASSESSOR]
        );

        $student1 = User::firstOrCreate(
            ['email' => 'student@iqbrave.com'],
            ['name' => 'Nimali Silva', 'password' => Hash::make('password'), 'role' => User::ROLE_STUDENT]
        );

        $student2 = User::firstOrCreate(
            ['email' => 'student2@iqbrave.com'],
            ['name' => 'Ruwan Fernando', 'password' => Hash::make('password'), 'role' => User::ROLE_STUDENT]
        );

        // 2. Create Realistic Courses
        $this->command->info('Creating NVQ Courses...');
        $courseICT = Course::firstOrCreate(
            ['slug' => 'nvq-ict-4'],
            [
                'title' => 'NVQ Level 4 - ICT Technician',
                'description' => 'Comprehensive NVQ Level 4 training covering hardware, software, and networking troubleshooting.',
                'instructor_id' => $instructor->id,
                'status' => 'published',
            ]
        );
        $courseICT->assignedInstructors()->syncWithoutDetaching([$instructor->id => ['role' => 'creator']]);

        $courseWeb = Course::firstOrCreate(
            ['slug' => 'nvq-web-4'],
            [
                'title' => 'Web Development Basics',
                'description' => 'Learn HTML, CSS, and basic JavaScript in this comprehensive NVQ-aligned course.',
                'instructor_id' => $instructor->id,
                'status' => 'published',
            ]
        );
        $courseWeb->assignedInstructors()->syncWithoutDetaching([$instructor->id => ['role' => 'creator']]);

        // Enroll Students
        Enrollment::firstOrCreate(['user_id' => $student1->id, 'course_id' => $courseICT->id], ['status' => 'active', 'enrolled_at' => now()]);
        Enrollment::firstOrCreate(['user_id' => $student1->id, 'course_id' => $courseWeb->id], ['status' => 'active', 'enrolled_at' => now()]);
        Enrollment::firstOrCreate(['user_id' => $student2->id, 'course_id' => $courseICT->id], ['status' => 'active', 'enrolled_at' => now()]);

        // 3. Create Modules, Units, Lessons for ICT Course
        $this->command->info('Building Curriculum...');
        $module1 = Module::firstOrCreate(
            ['course_id' => $courseICT->id, 'title' => 'Module 1: Hardware Troubleshooting'],
            ['description' => 'Learn how to diagnose and repair computer hardware issues.', 'order' => 1]
        );
        
        $unit1 = Unit::firstOrCreate(
            ['module_id' => $module1->id, 'title' => 'Unit 1.1: Motherboards and Processors'],
            ['description' => 'Understanding the core components of a PC.', 'order' => 1]
        );

        Lesson::firstOrCreate(
            ['unit_id' => $unit1->id, 'title' => 'Lesson 1: Diagnosing CPU Overheating'],
            ['content' => '<p>CPU overheating is a common issue. Symptoms include sudden system shutdowns and thermal throttling.</p><ul><li>Check thermal paste.</li><li>Ensure fan is spinning.</li></ul>', 'order' => 1, 'is_active' => true]
        );

        // 4. Create Gamification Stats for Students
        $this->command->info('Setting up Gamification profiles...');
        StudentGamificationStat::updateOrCreate(
            ['user_id' => $student1->id],
            ['total_xp' => 2450, 'current_streak' => 12, 'hearts' => 5]
        );

        StudentGamificationStat::updateOrCreate(
            ['user_id' => $student2->id],
            ['total_xp' => 450, 'current_streak' => 2, 'hearts' => 3]
        );

        // 5. Create Assignments & Pending Submissions for the Assessor to Grade
        $this->command->info('Creating Assignments & Submissions...');
        $assignment = Assignment::firstOrCreate(
            ['unit_id' => $unit1->id, 'title' => 'Practical Task: Build a PC'],
            [
                'description' => 'Write a comprehensive report on selecting components for an office PC. Upload as PDF.',
                'due_date' => now()->addDays(5),
                'max_marks' => 100
            ]
        );

        AssignmentSubmission::firstOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => $student1->id],
            [
                'file_path' => 'demo_submissions/nimali_report.pdf',
                'status' => 'submitted',
                'submitted_at' => now()->subDay(),
            ]
        );

        // 6. Seed NVQ-style Quiz
        $this->command->info('Seeding NVQ Quizzes...');
        $quiz = Quiz::firstOrCreate(
            ['unit_id' => $unit1->id, 'title' => 'Hardware Diagnostics Simulation'],
            ['description' => 'NVQ standard practical simulation quiz for Hardware Diagnostics.', 'is_active' => true]
        );

        if ($quiz->questions()->count() == 0) {
            $q1 = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_text' => 'Simulation Scenario: A client complains that their laptop battery drains extremely fast even when the laptop is closed. As an ICT Technician, which power management setting in the OS should you inspect first to resolve this issue?'
            ]);
            QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Screen brightness settings.', 'is_correct' => false]);
            QuizOption::create(['question_id' => $q1->id, 'option_text' => 'The "Lid Close Action" (Sleep vs. Hibernate vs. Do Nothing).', 'is_correct' => true]);
            QuizOption::create(['question_id' => $q1->id, 'option_text' => 'The BIOS boot order configuration.', 'is_correct' => false]);
        }

        $this->command->info('=============================================');
        $this->command->info('     PRESENTATION DEMO DATA READY!           ');
        $this->command->info('=============================================');
        $this->command->info('Admin:      admin@iqbrave.com      (pw: password)');
        $this->command->info('Instructor: instructor@iqbrave.com (pw: password)');
        $this->command->info('Assessor:   assessor@iqbrave.com   (pw: password)');
        $this->command->info('Student:    student@iqbrave.com    (pw: password)');
        $this->command->info('=============================================');
    }
}
