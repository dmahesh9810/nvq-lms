<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * List all courses for the currently logged in instructor.
     */
    public function index()
    {
        $courses = Auth::user()->instructedCourses()
            ->withCount('enrollments')
            ->latest()
            ->paginate(10);

        return view('instructor.courses.index', compact('courses'));
    }

    /**
     * Show create course form.
     */
    public function create()
    {
        return view('instructor.courses.create');
    }

    /**
     * Store a new course.
     */
    public function store(CourseRequest $request)
    {
        $data = $request->validated();
        $data['instructor_id'] = Auth::id();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course = Course::create($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Course created successfully!');
    }

    /**
     * Show single course with its modules/units.
     */
    public function show(Course $course)
    {
        $this->authorizeCourse($course);

        $course->load(['modules.units.lessons']);

        return view('instructor.courses.show', compact('course'));
    }

    /**
     * Show edit form.
     */
    public function edit(Course $course)
    {
        $this->authorizeCourse($course);

        return view('instructor.courses.edit', compact('course'));
    }

    /**
     * Update a course.
     */
    public function update(CourseRequest $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validated();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->update($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Course updated successfully!');
    }

    /**
     * Delete a course and its thumbnail.
     */
    public function destroy(Course $course)
    {
        $this->authorizeCourse($course);

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return redirect()
            ->route('instructor.courses.index')
            ->with('success', 'Course deleted successfully!');
    }

    /**
     * Ensure the logged-in instructor owns this course.
     */
    private function authorizeCourse(Course $course): void
    {
        if ($course->instructor_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'You do not own this course.');
        }
    }
}
