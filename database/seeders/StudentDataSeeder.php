<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get our randomly generated 50 students
        $students = User::where('role', 'student')->get();

        $course = Course::where('slug', 'computer-hardware-technician-nvq-level-4')->firstOrFail();

        // Eager load all required lesson and quiz entities
        $activeLessons = Lesson::where('is_active', true)
            ->whereHas('unit.module', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })->get();

        $activeQuizzes = Quiz::where('is_active', true)
            ->whereHas('unit.module', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })->get();

        foreach ($students as $index => $student) {
            // 1. Enroll the student
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'enrolled_at' => now()->subDays(rand(30, 90)),
                'status' => 'active'
            ]);

            // Determine Completion Level (Mix of 100% completions and dropouts)
            $completionPercentage = rand(10, 100);

            // Force exactly 50% of the class (25 students) to graduate completely for rich analytics
            if ($index < 25) {
                $completionPercentage = 100;
            }

            // 2. Mark Lessons as Completed
            $lessonsToComplete = (int)($activeLessons->count() * ($completionPercentage / 100));
            // Take the exact sequence of lessons they theoretically completed
            foreach ($activeLessons->take($lessonsToComplete) as $lesson) {
                LessonProgress::create([
                    'user_id' => $student->id,
                    'lesson_id' => $lesson->id,
                    'completed_at' => now()->subDays(rand(2, 25))
                ]);
            }

            // 3. Quiz Attempts & Certificate Generation
            if ($completionPercentage === 100) {
                // If 100%, they passed every quiz
                foreach ($activeQuizzes as $quiz) {
                    QuizAttempt::create([
                        'user_id' => $student->id,
                        'quiz_id' => $quiz->id,
                        'score' => rand(60, 100), // Pass mark is 50
                        'result' => 'PASS',
                        'started_at' => now()->subDays(rand(1, 10))->subMinutes(45),
                        'completed_at' => now()->subDays(rand(1, 10)),
                    ]);
                }

                // Issue NVQ Certificate
                Certificate::firstOrCreate([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                ], [
                    'certificate_number' => 'IQB-' . date('Y') . '-' . strtoupper(Str::random(6)),
                    'issued_at' => now(),
                    'status' => 'active'
                ]);
            }
            else {
                // Simulate a struggling student parsing through some quizzes
                $quizzesAttempted = rand(1, 8);
                foreach ($activeQuizzes->take($quizzesAttempted) as $quiz) {
                    $passed = rand(0, 1) === 1; // 50/50 chance of passing a quiz locally

                    QuizAttempt::create([
                        'user_id' => $student->id,
                        'quiz_id' => $quiz->id,
                        'score' => $passed ? rand(50, 100) : rand(10, 40),
                        'result' => $passed ? 'PASS' : 'FAIL',
                        'started_at' => now()->subDays(rand(1, 10))->subMinutes(30),
                        'completed_at' => now()->subDays(rand(1, 10)),
                    ]);

                    // If they failed, maybe they tried again
                    if (!$passed && rand(0, 1) === 1) {
                        QuizAttempt::create([
                            'user_id' => $student->id,
                            'quiz_id' => $quiz->id,
                            'score' => rand(60, 95), // Passed the second time
                            'result' => 'PASS',
                            'started_at' => now()->subDays(rand(1, 5))->subMinutes(35),
                            'completed_at' => now()->subDays(rand(1, 5)),
                        ]);
                    }
                }
            }
        }
    }
}
