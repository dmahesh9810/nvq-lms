<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\Assignment;
use App\Models\CompetencyAssessment;
use Illuminate\Support\Str;

class NvqDemoSeeder extends Seeder
{
    /**
     * Run the NVQ Demo Database Seeds.
     */
    public function run(): void
    {
        // 1. Ensure Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@iqbrave.com'],
            ['name' => 'John Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );
        $instructor = User::firstOrCreate(
            ['email' => 'instructor1@iqbrave.com'],
            ['name' => 'John Instructor', 'password' => bcrypt('password'), 'role' => 'instructor']
        );
        $student = User::firstOrCreate(
            ['email' => 'teststudent_manual@test.com'],
            ['name' => 'Test Student', 'password' => bcrypt('password'), 'role' => 'student']
        );
        $assessor = User::firstOrCreate(
            ['email' => 'assessor@example.com'],
            ['name' => 'Alex Assessor', 'password' => bcrypt('password'), 'role' => 'assessor']
        );

        // 2. Create NVQ Course
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Advanced Software Engineering NVQ Level 5',
            'slug' => 'advanced-software-engineering-nvq-level-5',
            'description' => 'A comprehensive, industry-standard NVQ Level 5 program covering full-stack development, database architecture, and systematic software testing.',
            'status' => 'published',
        ]);

        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        // 3. Modules & Units
        $structure = [
            'Module 1: Core Programming Architecture' => [
                'Unit 1: Object-Oriented Principles' => [
                    'nvq' => 'ICT/SE/5/01',
                    'lo' => '1. Understand OOP paradigms. 2. Apply abstraction and polymorphism.',
                    'pc' => '1.1 Classes are designed cleanly. 2.1 Patterns are utilized.',
                    'ac' => 'Direct observation, oral questioning.',
                    'lessons' => ['Introduction to OOP', 'Advanced Polymorphism', 'Interfaces vs Abstract Classes']
                ],
                'Unit 2: Design Patterns' => [
                    'nvq' => 'ICT/SE/5/02',
                    'lo' => '1. Identify common design patterns. 2. Implement Singleton and Factory.',
                    'pc' => '1.1 Patterns are selected correctly for the use case.',
                    'ac' => 'Project submission, written exam.',
                    'lessons' => ['Creational Patterns', 'Structural Patterns', 'Behavioral Patterns']
                ]
            ],
            'Module 2: Database Systems & APIs' => [
                'Unit 3: Relational Database Design' => [
                    'nvq' => 'ICT/SE/5/03',
                    'lo' => '1. Normalize databases to 3NF. 2. Write complex SQL joins.',
                    'pc' => '1.1 Schema handles 1M+ rows efficiently.',
                    'ac' => 'Practical database exam.',
                    'lessons' => ['Database Normalization', 'Advanced SQL Queries', 'Indexing and Performance']
                ],
                'Unit 4: RESTful API Development' => [
                    'nvq' => 'ICT/SE/5/04',
                    'lo' => '1. Build stateless APIs. 2. Secure endpoints with JWT.',
                    'pc' => '1.1 API meets REST standards. 2.1 Tokens are securely managed.',
                    'ac' => 'Code review, automated testing logs.',
                    'lessons' => ['REST Principles', 'API Authentication (JWT)', 'Rate Limiting and Security']
                ]
            ],
            'Module 3: Quality Assurance' => [
                'Unit 5: Automated Testing' => [
                    'nvq' => 'ICT/SE/5/05',
                    'lo' => '1. Write unit tests. 2. Setup CI/CD pipelines.',
                    'pc' => '1.1 Test coverage exceeds 80%.',
                    'ac' => 'Repository inspection, CI build logs.',
                    'lessons' => ['Unit Testing Basics', 'Mocking Dependencies', 'Continuous Integration (GitHub Actions)']
                ]
            ]
        ];

        $moduleOrder = 1;
        $unitOrderCounter = 1;
        $activeUnits = [];

        foreach ($structure as $modTitle => $units) {
            $module = Module::create([
                'course_id' => $course->id,
                'title' => $modTitle,
                'description' => 'Comprehensive module covering ' . $modTitle,
                'order' => $moduleOrder++,
                'is_active' => true,
            ]);

            $unitOrder = 1;
            foreach ($units as $unitTitle => $unitData) {
                $unit = Unit::create([
                    'module_id' => $module->id,
                    'title' => $unitTitle,
                    'description' => 'Detailed learning unit for ' . $unitTitle,
                    'order' => $unitOrder++,
                    'is_active' => true,
                    // NVQ Data
                    'nvq_unit_code' => $unitData['nvq'],
                    'nvq_level' => 5,
                    'learning_outcomes' => $unitData['lo'],
                    'performance_criteria' => $unitData['pc'],
                    'assessment_criteria' => $unitData['ac'],
                ]);
                $activeUnits[] = $unit;

                // Lessons
                $lessonOrder = 1;
                foreach ($unitData['lessons'] as $lessonTitle) {
                    $lesson = Lesson::create([
                        'unit_id' => $unit->id,
                        'title' => $lessonTitle,
                        'content' => '<p>This is the detailed content for ' . $lessonTitle . '. It covers everything you need to know to pass the performance criteria.</p>',
                        'order' => $lessonOrder++,
                        'is_active' => true,
                        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', // Demo video
                    ]);
                    
                    // Mark some lessons as completed
                    if ($unitOrderCounter < 4) {
                        $student->lessonProgress()->create([
                            'lesson_id' => $lesson->id,
                            'completed_at' => now()->subDays(rand(1, 5)),
                        ]);
                    }
                }

                // Quiz per unit
                $quiz = Quiz::create([
                    'unit_id' => $unit->id,
                    'title' => 'Quiz: ' . $unitTitle,
                    'description' => 'Test your knowledge on ' . $unitTitle,
                    'pass_mark' => 80,
                    'is_active' => true,
                ]);

                // Add 3 questions to quiz
                for ($i = 1; $i <= 3; $i++) {
                    $question = QuizQuestion::create([
                        'quiz_id' => $quiz->id,
                        'question_text' => 'Sample NVQ Question ' . $i . ' for ' . $unitTitle . '?',
                        'marks' => 10,
                    ]);

                    QuizOption::create(['question_id' => $question->id, 'option_text' => 'Correct Answer', 'is_correct' => true]);
                    QuizOption::create(['question_id' => $question->id, 'option_text' => 'Wrong Answer 1', 'is_correct' => false]);
                    QuizOption::create(['question_id' => $question->id, 'option_text' => 'Wrong Answer 2', 'is_correct' => false]);
                    QuizOption::create(['question_id' => $question->id, 'option_text' => 'Wrong Answer 3', 'is_correct' => false]);
                }

                $unitOrderCounter++;
            }
        }

        // 4. Competency Assessments (Partial Completion for UI Demo)
        // Unit 1 & 2: Competent
        // Unit 3: Not Competent
        // Unit 4 & 5: Not Assessed
        
        if (isset($activeUnits[0])) {
            CompetencyAssessment::create([
                'user_id' => $student->id,
                'unit_id' => $activeUnits[0]->id,
                'assessor_id' => $admin->id,
                'status' => 'competent',
                'remarks' => 'Excellent understanding of OOP.',
                'assessed_at' => now()->subDays(2),
            ]);
        }
        
        if (isset($activeUnits[1])) {
            CompetencyAssessment::create([
                'user_id' => $student->id,
                'unit_id' => $activeUnits[1]->id,
                'assessor_id' => $admin->id,
                'status' => 'competent',
                'remarks' => 'Good application of design patterns.',
                'assessed_at' => now()->subDays(1),
            ]);
        }

        if (isset($activeUnits[2])) {
            CompetencyAssessment::create([
                'user_id' => $student->id,
                'unit_id' => $activeUnits[2]->id,
                'assessor_id' => $admin->id,
                'status' => 'not_competent',
                'remarks' => 'Needs to improve SQL JOIN query performance and normalization concepts.',
                'assessed_at' => now(),
            ]);
        }
    }
}
