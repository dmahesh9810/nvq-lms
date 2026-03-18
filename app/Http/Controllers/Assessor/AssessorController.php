<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessorController extends Controller
{
    /**
     * Main Assessor Dashboard
     */
    public function dashboard()
    {
        $totalStudents = User::where('role', 'student')->count();
        $activeCourses = Course::where('status', 'published')->count();
        $pendingGrading = \App\Models\AssignmentSubmission::where('status', '!=', 'graded')->count();

        $totalEnrollments = Enrollment::count();

        // Calculate Average Progress system-wide
        $expectedLessons = DB::table('enrollments')
            ->join('modules', 'enrollments.course_id', '=', 'modules.course_id')
            ->join('units', 'modules.id', '=', 'units.module_id')
            ->join('lessons', 'units.id', '=', 'lessons.unit_id')
            ->count();
            
        $completedLessons = DB::table('lesson_progress')->count();
        
        $averageProgress = $expectedLessons > 0 ? round(($completedLessons / $expectedLessons) * 100) : 0;

        $stats = [
            'total_students' => $totalStudents,
            'active_courses' => $activeCourses,
            'average_progress' => $averageProgress,
            'pending_grading' => $pendingGrading,
        ];

        // Fetch recent enrollments for preview
        $recentEnrollments = Enrollment::with(['student', 'course'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.assessor', compact('stats', 'recentEnrollments'));
    }

    /**
     * Student Progress Monitoring List
     */
    public function students()
    {
        // Load enrollments
        $enrollments = Enrollment::with(['student', 'course'])
            ->latest()
            ->paginate(15);

        if ($enrollments->isNotEmpty()) {
            $userIds = $enrollments->pluck('user_id')->unique();
            $courseIds = $enrollments->pluck('course_id')->unique();

            // Count total lessons per course
            $lessonsPerCourse = DB::table('courses')
                ->join('modules', 'courses.id', '=', 'modules.course_id')
                ->join('units', 'modules.id', '=', 'units.module_id')
                ->join('lessons', 'units.id', '=', 'lessons.unit_id')
                ->whereIn('courses.id', $courseIds)
                ->where('lessons.is_active', true)
                ->groupBy('courses.id')
                ->select('courses.id', DB::raw('count(lessons.id) as total_lessons'))
                ->pluck('total_lessons', 'id');

            // Count completed lessons per user per course
            $completedPerUserCourse = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->join('units', 'lessons.unit_id', '=', 'units.id')
                ->join('modules', 'units.module_id', '=', 'modules.id')
                ->whereIn('lesson_progress.user_id', $userIds)
                ->whereIn('modules.course_id', $courseIds)
                ->groupBy('lesson_progress.user_id', 'modules.course_id')
                ->select('lesson_progress.user_id', 'modules.course_id', DB::raw('count(lesson_progress.lesson_id) as completed_lessons'))
                ->get()
                ->keyBy(function ($item) {
                    return $item->user_id . '_' . $item->course_id;
                });

            // Map stats back
            foreach ($enrollments as $enrollment) {
                $total = $lessonsPerCourse[$enrollment->course_id] ?? 0;
                $completedData = $completedPerUserCourse[$enrollment->user_id . '_' . $enrollment->course_id] ?? null;
                $completed = $completedData ? $completedData->completed_lessons : 0;
                
                $enrollment->total_lessons = $total;
                $enrollment->completed_lessons = $completed;
                $enrollment->progress_percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                $enrollment->pending_lessons = $total - $completed;
            }
        }

        return view('assessor.students.index', compact('enrollments'));
    }

    /**
     * Course Performance Analytics List
     */
    public function courses()
    {
        $courses = Course::with('instructor')
            ->withCount('enrollments')
            ->latest()
            ->paginate(15);
            
        if ($courses->isNotEmpty()) {
            $courseIds = $courses->pluck('id');

            // Total active lessons per course
            $lessonsPerCourse = DB::table('courses')
                ->join('modules', 'courses.id', '=', 'modules.course_id')
                ->join('units', 'modules.id', '=', 'units.module_id')
                ->join('lessons', 'units.id', '=', 'lessons.unit_id')
                ->whereIn('courses.id', $courseIds)
                ->where('lessons.is_active', true)
                ->groupBy('courses.id')
                ->select('courses.id', DB::raw('count(lessons.id) as total_lessons'))
                ->pluck('total_lessons', 'id');

            // Total completed lessons across all users per course
            $completedPerCourse = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->join('units', 'lessons.unit_id', '=', 'units.id')
                ->join('modules', 'units.module_id', '=', 'modules.id')
                ->whereIn('modules.course_id', $courseIds)
                ->groupBy('modules.course_id')
                ->select('modules.course_id', DB::raw('count(lesson_progress.lesson_id) as total_completed'))
                ->pluck('total_completed', 'course_id');

            foreach ($courses as $course) {
                $totalLessons = $lessonsPerCourse[$course->id] ?? 0;
                $expectedCompletes = $totalLessons * $course->enrollments_count;
                $actualCompletes = $completedPerCourse[$course->id] ?? 0;

                $course->average_progress = $expectedCompletes > 0 ? round(($actualCompletes / $expectedCompletes) * 100) : 0;
            }
        }

        return view('assessor.courses.index', compact('courses'));
    }
}
