<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class InstructorAnalyticsController extends Controller
{
    /**
     * Display the comprehensive instructor analytics dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // 1. Fetch courses owned by the instructor with deep aggregated relationships.
        // This query eliminates N+1 loading entirely by nesting `withCount` and `withAvg` at the engine level.
        $courses = $user->instructedCourses()
            ->withCount(['enrollments', 'certificates'])
            ->with([
            'modules.units.lessons' => function ($query) {
            $query->withCount(['progress as completions_count' => function ($subQuery) {
                    $subQuery->whereNotNull('completed_at');
                }
                    ]);
            },
            'modules.units.quizzes' => function ($query) {
            $query->withCount([
                    'attempts as total_attempts' => function ($subQuery) {
                $subQuery->whereNotNull('completed_at');
            }
                    ,
                    'attempts as passed_attempts' => function ($subQuery) {
                $subQuery->where('result', 'PASS')->whereNotNull('completed_at');
            }
                ])->withAvg(['attempts' => function ($subQuery) {
                $subQuery->whereNotNull('completed_at');
            }
                ], 'score');
        }
        ])->get();

        // 2. Map calculated metrics to each course
        $courses->each(function ($course) {

            // A. Tally total lessons across all nested structures
            $totalLessons = 0;
            $courseTotalCompletions = 0;

            foreach ($course->modules as $module) {
                foreach ($module->units as $unit) {
                    foreach ($unit->lessons as $lesson) {
                        $totalLessons++;
                        $courseTotalCompletions += $lesson->completions_count;
                    }
                }
            }

            // B. Learning Progress - Average Completion %
            // If 10 enrollments and 5 lessons, max possible completions is 50.
            // Avg Completion = (Actual Completions / Max Possible Completions) * 100
            $totalEnrollments = $course->enrollments_count;
            $maxPossibleCompletions = $totalEnrollments * $totalLessons;

            if ($maxPossibleCompletions > 0) {
                $course->average_completion_percentage = round(($courseTotalCompletions / $maxPossibleCompletions) * 100, 1);
            }
            else {
                $course->average_completion_percentage = 0;
            }

            // Attach pure counters for view iteration convenience
            $course->total_lessons_count = $totalLessons;
        });

        return view('instructor.analytics.dashboard', compact('courses'));
    }
}
