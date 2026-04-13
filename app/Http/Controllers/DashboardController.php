<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
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
            'total_users'        => User::count(),
            'total_students'     => User::where('role', 'student')->count(),
            'total_instructors'  => User::where('role', 'instructor')->count(),
            'total_courses'      => Course::count(),
            'published_courses'  => Course::where('status', 'published')->count(),
            'total_enrollments'  => Enrollment::count(),
            'pending_requests'   => ChangeRequest::where('status', 'pending')->count(),
        ];

        $pendingCourses   = Course::with('instructor')->where('status', 'pending')->latest()->get();
        $pendingRequests  = ChangeRequest::with('requester')->where('status', 'pending')->latest()->take(5)->get();

        return view('dashboard.admin', compact('stats', 'pendingCourses', 'pendingRequests'));
    }

    /**
     * Approve a pending course.
     */
    public function approveCourse(Course $course)
    {
        $course->update(['status' => 'published']);
        return back()->with('success', 'Course approved and published successfully.');
    }

    /**
     * Reject a pending course.
     */
    public function rejectCourse(Course $course)
    {
        $course->update(['status' => 'rejected']);
        return back()->with('success', 'Course rejected.');
    }

    /**
     * Instructor Dashboard
     * Shows statistics relevant to the logged-in instructor.
     */
    public function instructor()
    {
        $user = Auth::user();

        // 1. Fetch all visible courses (created, assigned directly, or assigned via modules)
        $userId = $user->id;
        $allCourses = Course::where('instructor_id', $userId)
            ->orWhereHas('assignedInstructors', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->orWhereHas('modules.assignedInstructors', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->withCount(['enrollments', 'modules'])
            ->get();

        $recentCourses = $allCourses->sortByDesc('created_at')->take(5);

        // 3. Fetch specific assigned modules with their parent courses
        $assignedModules = $user->assignedModules()
            ->with('course')
            ->latest()
            ->take(5)
            ->get();

        // 4. Calculate total distinct students across all accessible courses
        $courseIds = $allCourses->pluck('id')->toArray();
        $totalStudents = empty($courseIds) ? 0 : Enrollment::whereIn('course_id', $courseIds)->distinct()->count('user_id');

        $stats = [
            'my_courses' => $allCourses->count(),
            'published_courses' => $allCourses->where('status', 'published')->count(),
            'total_students' => $totalStudents,
        ];

        return view('dashboard.instructor', compact('stats', 'recentCourses', 'assignedModules'));
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
            'pending_grading' => \App\Models\AssignmentSubmission::where('status', '!=', 'graded')->count(),
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
        $courses = $user->courses()->with(['modules.units.lessons'])->get();

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
