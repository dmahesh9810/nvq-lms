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
use App\Models\QuizAttempt;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CompetencyAssessment;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Carbon\Carbon;
use Illuminate\Support\Str;

class NcsRealSystemSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $baseDate = Carbon::now()->subMonths(7);

        $this->command->info('Creating Users...');

        // 1. Create Core Staff
        $admin = User::firstOrCreate(['email' => 'admin@iqbrave.com'], [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $instructors = [];
        for ($i = 1; $i <= 5; $i++) {
            $instructors[] = User::firstOrCreate(['email' => "instructor{$i}@iqbrave.com"], [
                'name' => $faker->name . ' (Instructor)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_INSTRUCTOR,
            ]);
        }

        $assessors = [];
        for ($i = 1; $i <= 3; $i++) {
            $assessors[] = User::firstOrCreate(['email' => "assessor{$i}@iqbrave.com"], [
                'name' => $faker->name . ' (Assessor)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ASSESSOR,
            ]);
        }

        // 2. Create Students
        $students = [];
        $this->command->info('Creating 100 Students...');
        for ($i = 1; $i <= 100; $i++) {
            $students[] = User::create([
                'name' => $faker->name,
                'email' => "student{$i}@iqbrave.com",
                'password' => Hash::make('password'),
                'role' => User::ROLE_STUDENT,
                'created_at' => $faker->dateTimeBetween($baseDate, 'now'),
            ]);
        }

        // 3. Define the NCS Course Structures
        $ncsCourses = [
            [
                'title' => 'Information & Communication Technology Technician',
                'description' => 'Comprehensive NVQ Level 4 ICT Technician program covering software, networking, and safety fundamentals.',
                'instructor' => $instructors[0]->id,
                'thumbnail' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=640',
                'modules' => [
                    ['title' => 'Occupational Health & Safety', 'units' => ['Identify OH&S requirements', 'Implement safety procedures']],
                    ['title' => 'Computer Basics & OS', 'units' => ['Install Windows OS', 'Manage files & folders', 'Linux Fundamentals']],
                    ['title' => 'Office Applications', 'units' => ['Word Processing', 'Spreadsheet Management', 'Presentation Skills']],
                ]
            ],
            [
                'title' => 'Computer Hardware & Network Technician',
                'description' => 'NVQ Level 4 certification for assembling PCs, troubleshooting hardware, and setting up LAN/WAN networks.',
                'instructor' => $instructors[1]->id,
                'thumbnail' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?q=80&w=640',
                'modules' => [
                    ['title' => 'Hardware Assembly', 'units' => ['Motherboard components', 'Power Supply Integration', 'Peripherals setup']],
                    ['title' => 'Troubleshooting', 'units' => ['Diagnostic tools', 'Boot errors', 'Hardware part replacements']],
                    ['title' => 'Networking', 'units' => ['Crimping cables', 'Router configuration', 'IP Subnetting']],
                ]
            ],
            [
                'title' => 'Graphic Designer',
                'description' => 'Master digital design using industry-standard tools for vector and raster graphics (NVQ Level 4).',
                'instructor' => $instructors[2]->id,
                'thumbnail' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?q=80&w=640',
                'modules' => [
                    ['title' => 'Design Principles', 'units' => ['Color Theory', 'Typography', 'Layout & Composition']],
                    ['title' => 'Raster Graphics (Photoshop)', 'units' => ['Photo Retouching', 'Masking & Layers', 'Digital Painting']],
                    ['title' => 'Vector Graphics (Illustrator)', 'units' => ['Pen Tool Mastery', 'Logo Design', 'Print preparation']],
                ]
            ]
        ];

        $this->command->info('Building Courses & Lessons...');
        $courses = [];
        foreach ($ncsCourses as $idx => $nc) {
            $course = Course::create([
                'instructor_id' => $nc['instructor'],
                'title' => $nc['title'],
                'slug' => Str::slug($nc['title']) . '-' . rand(100,999),
                'description' => $nc['description'],
                'thumbnail' => $nc['thumbnail'],
                'status' => 'published',
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
            ]);
            $courses[] = $course;

            foreach ($nc['modules'] as $mIdx => $mod) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => $mod['title'],
                    'order' => $mIdx + 1,
                    'is_active' => true,
                ]);

                foreach ($mod['units'] as $uIdx => $unitTitle) {
                    $unit = Unit::create([
                        'module_id' => $module->id,
                        'title' => $unitTitle,
                        'nvq_unit_code' => 'NCS'.($idx+1).'-M'.($mIdx+1).'-U'.($uIdx+1),
                        'order' => $uIdx + 1,
                        'is_active' => true,
                    ]);

                    // Add 3 Lessons per Unit
                    for ($l = 1; $l <= 3; $l++) {
                        Lesson::create([
                            'unit_id' => $unit->id,
                            'title' => "Lesson {$l} for {$unitTitle}",
                            'content' => $faker->paragraphs(3, true),
                            'order' => $l,
                            'is_active' => true,
                        ]);
                    }

                    // Add 1 Assignment per Unit
                    Assignment::create([
                        'unit_id' => $unit->id,
                        'title' => "Practical Evaluation: {$unitTitle}",
                        'description' => 'Complete the practical task as per NVQ guidelines and submit your report.',
                        'due_date' => Carbon::now()->addMonths(1),
                        'max_marks' => 100,
                        'is_active' => true,
                    ]);

                    // Add 1 Quiz per Unit
                    $quiz = Quiz::create([
                        'unit_id' => $unit->id,
                        'title' => "Quiz: {$unitTitle}",
                        'description' => "Test your knowledge on {$unitTitle}.",
                        'pass_mark' => 50,
                        'is_active' => true,
                    ]);

                    // Add 2 Questions to the Quiz
                    for ($q = 1; $q <= 2; $q++) {
                        $question = QuizQuestion::create([
                            'quiz_id' => $quiz->id,
                            'question_text' => "Sample Question $q for {$unitTitle}?",
                            'marks' => 10,
                        ]);

                        QuizOption::create(['question_id' => $question->id, 'option_text' => 'Option A', 'is_correct' => true]);
                        QuizOption::create(['question_id' => $question->id, 'option_text' => 'Option B', 'is_correct' => false]);
                        QuizOption::create(['question_id' => $question->id, 'option_text' => 'Option C', 'is_correct' => false]);
                        QuizOption::create(['question_id' => $question->id, 'option_text' => 'Option D', 'is_correct' => false]);
                    }
                }
            }
        }

        $this->command->info('Simulating 7-Month Realistic Activity...');

        // 4. Simulate Activity
        foreach ($students as $student) {
            // Enroll in 1 or 2 courses
            $numCourses = rand(1, 2);
            $enrolledCourses = $faker->randomElements($courses, $numCourses);

            foreach ($enrolledCourses as $course) {
                // Enrollment date at least a few days after student creation
                $enrollDate = Carbon::parse($student->created_at)->addDays(rand(1, 5));
                
                Enrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'enrolled_at' => $enrollDate,
                    'status' => 'active',
                ]);

                // Determine student's progress in this course: 10% to 100%
                $progressPercent = rand(10, 100);
                $allLessons = Lesson::whereHas('unit.module', function($q) use ($course) {
                    $q->where('course_id', $course->id);
                })->orderBy('id')->get();

                $lessonsToComplete = (int) ceil(($progressPercent / 100) * $allLessons->count());
                
                $cursorDate = clone $enrollDate;

                for ($i = 0; $i < $lessonsToComplete; $i++) {
                    $lesson = $allLessons[$i];
                    $cursorDate->addHours(rand(12, 72)); // Spread out completion times

                    if ($cursorDate->isFuture()) {
                        $cursorDate = Carbon::now()->subHours(rand(1,5));
                    }

                    LessonProgress::create([
                        'user_id' => $student->id,
                        'lesson_id' => $lesson->id,
                        'completed_at' => $cursorDate,
                    ]);

                    // If they complete the last lesson of a unit, submit assignment
                    $isLastInUnit = ($i + 1 == $allLessons->count() || $allLessons[$i+1]->unit_id != $lesson->unit_id);
                    if ($isLastInUnit) {
                        $assignment = Assignment::where('unit_id', $lesson->unit_id)->first();
                        if ($assignment) {
                            $subDate = clone $cursorDate;
                            $subDate->addDays(rand(1, 4));

                            if ($subDate->isPast()) {
                                $submission = AssignmentSubmission::create([
                                    'assignment_id' => $assignment->id,
                                    'user_id' => $student->id,
                                    'file_path' => 'submissions/practical-demo-file.pdf',
                                    'submitted_at' => clone $subDate,
                                    'status' => 'assessor_verified',
                                    'instructor_id' => $course->instructor_id,
                                    'instructor_review' => 'Good work, forwarded to assessor.',
                                    'instructor_competency_status' => 'competent',
                                    'instructor_reviewed_at' => clone $subDate->addDays(1),
                                ]);

                                // Assessor grades it
                                $assessor = $assessors[array_rand($assessors)];
                                CompetencyAssessment::create([
                                    'user_id' => $student->id,
                                    'unit_id' => $lesson->unit_id,
                                    'assessor_id' => $assessor->id,
                                    'status' => 'competent',
                                    'remarks' => 'Meets NVQ criteria.',
                                    'assessed_at' => clone $subDate->addDays(2),
                                ]);

                                // Update submission status
                                $submission->update([
                                    'assessor_id' => $assessor->id,
                                    'assessor_verification_note' => 'Verified competent.',
                                    'verified_at' => clone $subDate->addDays(2)
                                ]);
                            }
                        }
                    }
                }

                // If 100% Complete, issue certificate
                if ($progressPercent == 100 && $cursorDate->isPast()) {
                    Certificate::create([
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'certificate_number' => 'CERT-' . strtoupper(Str::random(8)) . '-' . $student->id,
                        'issued_at' => clone $cursorDate->addDays(1),
                        'status' => 'active'
                    ]);
                }
            }
        }
        
        $this->command->info('7-Month NCS Seed Completed Successfully!');
    }
}
