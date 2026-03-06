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
        // Fetch 4 latest active courses
        $courses = Course::with('instructor')
            ->where('status', 'published') // assuming 'published' or active status, fallback to all if needed
            ->latest()
            ->take(4)
            ->get();

        // If the query returns 0 (maybe 'status' is not published but '1' or 'active'), fallback to latest 4
        if ($courses->isEmpty()) {
            $courses = Course::with('instructor')->latest()->take(4)->get();
        }

        return view('home', compact('courses'));
    }

    /**
     * Display all public courses.
     */
    public function courses()
    {
        $courses = Course::with('instructor')->latest()->paginate(12);
        return view('courses.index', compact('courses'));
    }

    /**
     * Display a specific course details publicly.
     */
    public function showCourse($id)
    {
        $course = Course::with(['instructor', 'modules.units.lessons'])->findOrFail($id);
        return view('courses.show', compact('course'));
    }
}
