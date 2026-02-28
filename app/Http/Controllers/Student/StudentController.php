<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Browse all published courses available for enrollment.
     */
    public function browseCourses()
    {
        $enrolledIds = Auth::user()->enrolledCourses()->pluck('courses.id');

        $courses = Course::where('status', 'published')
            ->whereNotIn('id', $enrolledIds)
            ->with('instructor')
            ->withCount('enrollments')
            ->paginate(12);

        return view('student.courses.browse', compact('courses'));
    }

    /**
     * Enroll the current student in a course.
     */
    public function enroll(Course $course)
    {
        $user = Auth::user();

        // Prevent duplicate enrollment
        if ($user->enrolledCourses()->where('courses.id', $course->id)->exists()) {
            return redirect()
                ->route('student.courses.browse')
                ->with('info', 'You are already enrolled in this course.');
        }

        StudentEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        return redirect()
            ->route('student.courses.show', $course)
            ->with('success', 'Successfully enrolled! Start learning now.');
    }

    /**
     * Show the enrolled course with its full module/unit/lesson structure.
     */
    public function showCourse(Course $course)
    {
        $this->authorizeEnrollment($course);

        $course->load(['modules.units.lessons' => function ($q) {
            $q->where('is_active', true)->orderBy('order');
        }]);

        $user = Auth::user();

        // Build a flat list of completed lesson IDs for this student
        $completedLessonIds = LessonProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->toArray();

        $progress = $course->progressForStudent($user->id);

        return view('student.courses.show', compact('course', 'completedLessonIds', 'progress'));
    }

    /**
     * Show the lesson player for a specific lesson.
     */
    public function showLesson(Course $course, Lesson $lesson)
    {
        $this->authorizeEnrollment($course);

        $lesson->load('unit.module');

        $user = Auth::user();
        $isCompleted = $lesson->isCompletedByUser($user->id);

        // Load all lessons in the same unit for navigation
        $siblingLessons = $lesson->unit->lessons()->where('is_active', true)->orderBy('order')->get();
        $currentIndex = $siblingLessons->search(fn($l) => $l->id === $lesson->id);

        $prevLesson = $currentIndex > 0 ? $siblingLessons[$currentIndex - 1] : null;
        $nextLesson = ($currentIndex < $siblingLessons->count() - 1) ? $siblingLessons[$currentIndex + 1] : null;

        $progress = $course->progressForStudent($user->id);

        return view('student.lessons.show', compact(
            'course', 'lesson', 'isCompleted', 'prevLesson', 'nextLesson', 'progress'
        ));
    }

    /**
     * Mark a lesson as completed (or re-mark if already done).
     */
    public function markComplete(Course $course, Lesson $lesson)
    {
        $this->authorizeEnrollment($course);

        $user = Auth::user();

        // Using updateOrCreate to handle re-submissions gracefully
        LessonProgress::updateOrCreate(
        [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ],
        [
            'completed_at' => now(),
        ]
        );

        return redirect()
            ->route('student.lessons.show', [$course, $lesson])
            ->with('success', 'Lesson marked as completed!');
    }

    /**
     * Ensure the currently logged in student is enrolled in the given course.
     */
    private function authorizeEnrollment(Course $course): void
    {
        $enrolled = Auth::user()->enrolledCourses()
            ->where('courses.id', $course->id)
            ->exists();

        if (!$enrolled) {
            abort(403, 'You are not enrolled in this course.');
        }
    }
}
