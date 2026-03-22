<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\AssignmentResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    /**
     * Display a filtered list of student progress across courses.
     */
    public function index(Request $request)
    {
        // 1. Fetch lookup data for filters
        $students = User::where('role', 'student')->orderBy('name')->get();
        $courses = Course::where('status', 'published')->orderBy('title')->get();

        // 2. Base Query: Enrollments with eager loading
        $query = Enrollment::with(['student:id,name,email', 'course:id,title,thumbnail'])
            ->when($request->student_id, function ($q) use ($request) {
                return $q->where('user_id', $request->student_id);
            })
            ->when($request->course_id, function ($q) use ($request) {
                return $q->where('course_id', $request->course_id);
            })
            ->when($request->from_date, function ($q) use ($request) {
                return $q->whereDate('enrolled_at', '>=', $request->from_date);
            })
            ->when($request->to_date, function ($q) use ($request) {
                return $q->whereDate('enrolled_at', '<=', $request->to_date);
            });

        // Optional Role Restriction: If Assessor is tied to specific courses, filter here
        // e.g., ->whereIn('course_id', $assessorCourseIds)

        $enrollments = $query->orderByDesc('enrolled_at')->paginate(20)->withQueryString();

        // 3. Optimized Progress Calculation (Batch processing to avoid N+1)
        if ($enrollments->isNotEmpty()) {
            $this->attachProgressStats($enrollments);
        }

        return view('assessor.progress.index', compact('enrollments', 'students', 'courses'));
    }

    /**
     * Display detailed progress for a specific student in a specific course.
     */
    public function show(User $student, Course $course)
    {
        abort_unless($student->role === 'student', 404, 'User is not a student.');

        $enrollment = Enrollment::where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        // 1. Fetch Lessons with eager-loaded student progress
        $lessons = Lesson::whereHas('unit.module', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->where('is_active', true)
            ->with(['unit.module', 'progress' => function ($q) use ($student) {
                $q->where('user_id', $student->id);
            }])
            ->get();

        // Calculate progress percentage
        $totalLessons = $lessons->count();
        $completedLessons = $lessons->filter(function ($lesson) {
            return $lesson->progress->isNotEmpty();
        })->count();
        $progressPercent = $totalLessons > 0 ? (int)round(($completedLessons / $totalLessons) * 100) : 0;

        // 2. Fetch Quiz Attempts
        $quizAttempts = QuizAttempt::where('user_id', $student->id)
            ->whereHas('quiz.unit.module', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->with('quiz')
            ->latest()
            ->get();

        // 3. Fetch Assignment Results
        $assignmentResults = AssignmentResult::whereHas('submission', function ($q) use ($student, $course) {
                $q->where('user_id', $student->id)
                  ->whereHas('assignment.unit.module', function ($q2) use ($course) {
                      $q2->where('course_id', $course->id);
                  });
            })
            ->with(['submission.assignment', 'assessor'])
            ->latest('graded_at')
            ->get();

        return view('assessor.progress.detail', compact(
            'student', 'course', 'enrollment',
            'lessons', 'quizAttempts', 'assignmentResults',
            'progressPercent', 'totalLessons', 'completedLessons'
        ));
    }

    /**
     * Batch attaches total and completed lesson stats to a collection of enrollments.
     */
    private function attachProgressStats($enrollments)
    {
        $userIds = $enrollments->pluck('user_id')->unique();
        $courseIds = $enrollments->pluck('course_id')->unique();

        // 1. Total lessons per course (Batch query)
        $lessonsPerCourse = DB::table('lessons')
            ->join('units', 'lessons.unit_id', '=', 'units.id')
            ->join('modules', 'units.module_id', '=', 'modules.id')
            ->whereIn('modules.course_id', $courseIds)
            ->where('lessons.is_active', true)
            ->groupBy('modules.course_id')
            ->select('modules.course_id', DB::raw('count(lessons.id) as total'))
            ->pluck('total', 'course_id');

        // 2. Completed lessons per student-course (Batch query)
        $completedPerUserCourse = DB::table('lesson_progress')
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->join('units', 'lessons.unit_id', '=', 'units.id')
            ->join('modules', 'units.module_id', '=', 'modules.id')
            ->whereIn('lesson_progress.user_id', $userIds)
            ->whereIn('modules.course_id', $courseIds)
            ->groupBy('lesson_progress.user_id', 'modules.course_id')
            ->select('lesson_progress.user_id', 'modules.course_id', DB::raw('count(lesson_progress.lesson_id) as completed'))
            ->get()
            ->keyBy(function ($item) {
                return $item->user_id . '_' . $item->course_id;
            });

        // 3. Attach stats to collection items
        foreach ($enrollments as $enrollment) {
            $total = $lessonsPerCourse[$enrollment->course_id] ?? 0;
            $completedKey = $enrollment->user_id . '_' . $enrollment->course_id;
            $completedData = $completedPerUserCourse[$completedKey] ?? null;
            $completed = $completedData ? $completedData->completed : 0;

            $enrollment->total_lessons = $total;
            $enrollment->completed_lessons = $completed;
            $enrollment->progress_percentage = $total > 0 ? (int)round(($completed / $total) * 100) : 0;
        }
    }
}
