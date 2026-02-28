<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard
     * Shows system-wide statistics for administration.
     */
    public function admin()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'total_courses' => Course::count(),
            'published_courses' => Course::where('status', 'published')->count(),
            'total_enrollments' => StudentEnrollment::count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    /**
     * Instructor Dashboard
     * Shows statistics relevant to the logged-in instructor.
     */
    public function instructor()
    {
        $user = Auth::user();

        $courses = $user->instructedCourses()->withCount(['enrollments', 'modules'])->latest()->take(5)->get();

        $stats = [
            'my_courses' => $user->instructedCourses()->count(),
            'published_courses' => $user->instructedCourses()->where('status', 'published')->count(),
            'total_students' => StudentEnrollment::whereIn(
            'course_id', $user->instructedCourses()->pluck('id')
        )->distinct('user_id')->count(),
        ];

        return view('dashboard.instructor', compact('stats', 'courses'));
    }

    /**
     * Assessor Dashboard
     * Shows assessment-related overview.
     */
    public function assessor()
    {
        $stats = [
            'total_courses' => Course::where('status', 'published')->count(),
            'total_students' => User::where('role', 'student')->count(),
        ];

        return view('dashboard.assessor', compact('stats'));
    }

    /**
     * Student Dashboard
     * Shows the logged-in student's enrolled courses and progress.
     */
    public function student()
    {
        $user = Auth::user();
        $courses = $user->enrolledCourses()->with(['modules.units.lessons'])->get();

        // Calculate progress for each enrolled course
        $courseProgress = $courses->mapWithKeys(function ($course) use ($user) {
            return [$course->id => $course->progressForStudent($user->id)];
        });

        $stats = [
            'enrolled_courses' => $courses->count(),
            'active_enrollments' => $user->enrollments()->where('status', 'active')->count(),
        ];

        return view('dashboard.student', compact('stats', 'courses', 'courseProgress'));
    }
}
