<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Enrollment;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Browse all published courses available for enrollment.
     */
    public function browseCourses()
    {
        $courses = Course::where('status', 'published')
            ->whereDoesntHave('enrollments', function ($q) {
            $q->where('user_id', Auth::id());
        })
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
        if ($user->courses()->where('courses.id', $course->id)->exists()) {
            return redirect()
                ->route('student.courses.browse')
                ->with('info', 'You are already enrolled in this course.');
        }

        Enrollment::create([
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

        // Eager-load lesson's unit + module for breadcrumb
        $lesson->load('unit.module');

        // Eager-load full course structure for the sidebar navigation
        $course->load(['modules.units.lessons' => function ($q) {
            $q->where('is_active', true)->orderBy('order');
        }, 'modules.units' => function ($q) {
            $q->where('is_active', true)->orderBy('order');
        }, 'modules' => function ($q) {
            $q->where('is_active', true)->orderBy('order');
        }]);

        $user = Auth::user();
        $isCompleted = $lesson->isCompletedByUser($user->id);

        // Get all active lessons in the same unit for prev/next navigation
        $siblingLessons = $lesson->unit->lessons()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
        $currentIndex = $siblingLessons->search(fn($l) => $l->id === $lesson->id);

        $prevLesson = $currentIndex > 0 ? $siblingLessons[$currentIndex - 1] : null;
        $nextLesson = ($currentIndex < $siblingLessons->count() - 1)
            ? $siblingLessons[$currentIndex + 1]
            : null;

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

        // Check eligibility for certificate
        $certService = app(CertificateService::class);
        $status = $certService->getEligibilityStatus($user, $course);

        $msg = 'Lesson marked as completed!';
        $warning = null;

        if ($status['is_eligible']) {
            $certificate = $certService->checkAndIssueCertificate($user, $course);
            if ($certificate) {
                $msg .= ' Congratulations! You have completed all course requirements and earned a certificate!';
            }
        }
        else {
            $warning = $status['reasons'][0] ?? null;
        }

        $redirect = redirect()
            ->route('student.lessons.show', [$course, $lesson])
            ->with('success', $msg);

        if ($warning) {
            $redirect->with('info', $warning);
        }

        return $redirect;
    }

    /**
     * Ensure the currently logged in student is enrolled in the given course.
     */
    private function authorizeEnrollment(Course $course): void
    {
        abort_unless(
            $course->enrollments()->where('user_id', Auth::id())->exists(),
            403,
            'You are not enrolled in this course.'
        );
    }
}
