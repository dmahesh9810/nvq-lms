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
        $userId = \Illuminate\Support\Facades\Auth::id();
        
        // Fetch courses where user is primary OR assigned via pivot OR assigned to any module
        $courses = Course::where('instructor_id', $userId)
            ->orWhereHas('assignedInstructors', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->orWhereHas('modules.assignedInstructors', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
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
        abort_unless(Auth::user()->isAdmin(), 403, 'Only administrators can create courses.');

        return view('instructor.courses.create');
    }

    /**
     * Store a new course.
     */
    public function store(CourseRequest $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403, 'Only administrators can create courses.');

        $data = $request->validated();
        $data['instructor_id'] = Auth::id();
        $data['status'] = 'draft'; // Enforce draft status on creation

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course = Course::create($data);

        // Auto-assign the creator to course_user pivot with role 'creator'
        $course->assignedInstructors()->attach(Auth::id(), ['role' => 'creator']);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Course created successfully as draft!');
    }

    /**
     * Show single course with its modules/units.
     */
    public function show(Course $course)
    {
        $this->authorizeCourse($course);

        $course->load(['modules.units.lessons', 'assignedInstructors', 'modules.assignedInstructors']);

        $allInstructors = collect();
        if (\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            $allInstructors = \App\Models\User::whereIn('role', ['admin', 'instructor'])->get();
        }

        return view('instructor.courses.show', compact('course', 'allInstructors'));
    }

    /**
     * Show edit form.
     * Admins can edit directly; instructors must use the change request flow.
     */
    public function edit(Course $course)
    {
        $this->authorizeCourseStrict($course);

        abort_unless(
            Auth::user()->isAdmin(),
            403,
            'Instructors cannot edit courses directly. Please use the "Request Edit" button on the course page.'
        );

        if ($course->status === 'pending') {
            return redirect()->route('instructor.courses.show', $course)
                ->with('error', 'You cannot edit a course while it is pending approval.');
        }

        return view('instructor.courses.edit', compact('course'));
    }

    /**
     * Update a course.
     * Admin-only: instructors must use the change request flow.
     */
    public function update(CourseRequest $request, Course $course)
    {
        $this->authorizeCourseStrict($course);

        abort_unless(
            Auth::user()->isAdmin(),
            403,
            'Instructors cannot update courses directly. Please submit a Request Edit instead.'
        );

        if ($course->status === 'pending') {
            return redirect()->route('instructor.courses.show', $course)
                ->with('error', 'You cannot edit a course while it is pending approval.');
        }

        $data = $request->validated();
        unset($data['status']); // Security: Instructors cannot mass-assign status

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
     * Submit a course for admin review.
     */
    public function submitForReview(Course $course)
    {
        $this->authorizeCourseStrict($course);

        if ($course->status !== 'draft' && $course->status !== 'rejected') {
            return back()->with('error', 'Only draft or rejected courses can be submitted for review.');
        }

        $course->update(['status' => 'pending']);

        return back()->with('success', 'Course submitted for review successfully! Admin will verify it soon.');
    }

    /**
     * Delete a course and its thumbnail.
     * Admin-only: instructors must use the change request flow.
     */
    public function destroy(Course $course)
    {
        $this->authorizeCourseStrict($course);

        abort_unless(
            Auth::user()->isAdmin(),
            403,
            'Instructors cannot delete courses directly. Please submit a Request Delete instead.'
        );

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
        $userId = \Illuminate\Support\Facades\Auth::id();
        $isAdmin = \Illuminate\Support\Facades\Auth::user()->isAdmin();

        if ($course->instructor_id === $userId || $isAdmin) {
            return;
        }

        if ($course->assignedInstructors()->where('users.id', $userId)->exists()) {
            return;
        }

        if ($course->modules()->whereHas('assignedInstructors', function($q) use ($userId) {
            $q->where('users.id', $userId);
        })->exists()) {
            return;
        }

        abort(403, 'You do not own this course and are not assigned to it.');
    }

    private function authorizeCourseStrict(Course $course): void
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $isAdmin = \Illuminate\Support\Facades\Auth::user()->isAdmin();

        if ($course->instructor_id === $userId || $isAdmin) {
            return;
        }

        if ($course->assignedInstructors()->where('users.id', $userId)->exists()) {
            return;
        }

        abort(403, 'You do not have permission to manage this entire course.');
    }
}
