<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    /**
     * Display the student dashboard with learning analytics.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Enrolled Courses with Progress
        $courses = $user->enrolledCourses()->with(['modules.units.lessons', 'modules.units.quizzes'])->get();

        $enrolledCourseIds = $courses->pluck('id');

        // Course Progress mapping
        $courseProgress = $courses->mapWithKeys(function ($course) use ($user) {
            return [$course->id => $course->progressForStudent($user->id)];
        });

        // 2. Learning Progress Analytics
        $totalLessons = 0;
        foreach ($courses as $course) {
            $totalLessons += $course->totalLessons();
        }

        $totalLessonsCompleted = $user->lessonProgress()
            ->whereHas('lesson', function ($q) {
            $q->where('is_active', true);
        })
            ->whereNotNull('completed_at')
            ->count();

        $totalQuizzes = \App\Models\Quiz::whereHas('unit.module', function ($q) use ($enrolledCourseIds) {
            $q->whereIn('course_id', $enrolledCourseIds);
        })->where('is_active', true)->count();

        // We count distinct passed quizzes
        $totalQuizzesPassed = QuizAttempt::where('user_id', $user->id)
            ->where('result', 'PASS')
            ->whereNotNull('completed_at')
            ->distinct('quiz_id')
            ->count('quiz_id');

        // 3. Quiz Results History
        $quizAttempts = QuizAttempt::with('quiz')
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get();

        // 4. Certificates
        $certificates = Certificate::with('course')
            ->where('user_id', $user->id)
            ->orderByDesc('issued_at')
            ->get();

        return view('student.dashboard', compact(
            'courses',
            'courseProgress',
            'totalLessons',
            'totalLessonsCompleted',
            'totalQuizzes',
            'totalQuizzesPassed',
            'quizAttempts',
            'certificates'
        ));
    }
}
