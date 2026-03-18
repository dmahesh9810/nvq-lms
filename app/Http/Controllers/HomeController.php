<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        // Fetch 4 latest active published courses
        $courses = Course::with('instructor')
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        return view('home', compact('courses'));
    }

    /**
     * Display all public courses.
     */
    public function courses()
    {
        $courses = Course::with('instructor')
            ->where('status', 'published')
            ->latest()
            ->paginate(12);
        return view('courses.index', compact('courses'));
    }

    /**
     * Display a specific course details publicly.
     */
    public function showCourse($id)
    {
        $course = Course::with(['instructor', 'modules.units.lessons'])
            ->where('status', 'published')
            ->findOrFail($id);
        return view('courses.show', compact('course'));
    }
}
