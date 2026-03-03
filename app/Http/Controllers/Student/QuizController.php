<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /** List all active quizzes from enrolled courses */
    public function index()
    {
        $user = Auth::user();
        $enrolledCourseIds = $user->enrolledCourses()->pluck('courses.id');

        $quizzes = Quiz::whereHas('unit.module', function ($q) use ($enrolledCourseIds) {
            $q->whereIn('course_id', $enrolledCourseIds);
        })
            ->where('is_active', true)
            ->with('unit.module.course')
            ->withCount('questions')
            ->latest()
            ->paginate(15);

        // Latest attempt keyed by quiz_id
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->latest()
            ->get()
            ->unique('quiz_id')
            ->keyBy('quiz_id');

        return view('student.quizzes.index', compact('quizzes', 'attempts'));
    }

    /** Show the quiz taking page (all questions at once) */
    public function take(Quiz $quiz)
    {
        $this->authorizeAccess($quiz);

        // If already passed, redirect to previous result
        $lastAttempt = $quiz->lastAttemptByUser(Auth::id());
        if ($lastAttempt && $lastAttempt->passed) {
            return redirect()->route('student.quizzes.result', [$quiz, $lastAttempt])
                ->with('info', 'You have already passed this quiz.');
        }

        $quiz->load('questions.options');

        return view('student.quizzes.take', compact('quiz', 'lastAttempt'));
    }

    /** Auto-mark and record the quiz attempt */
    public function submit(Request $request, Quiz $quiz)
    {
        $this->authorizeAccess($quiz);

        // Block direct POST if student already passed this quiz
        $lastAttempt = $quiz->lastAttemptByUser(Auth::id());
        if ($lastAttempt && $lastAttempt->passed) {
            return redirect()->route('student.quizzes.result', [$quiz, $lastAttempt])
                ->with('info', 'You have already passed this quiz.');
        }

        $quiz->load('questions.options');

        $score = 0;
        $totalMarks = 0;

        foreach ($quiz->questions as $question) {
            $totalMarks += $question->marks;
            $selectedOptionId = (int)$request->input("answers.{$question->id}");
            $correctOption = $question->correctOption();

            if ($correctOption && $selectedOptionId === (int)$correctOption->id) {
                $score += $question->marks;
            }
        }

        $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100, 2) : 0;
        $passed = $percentage >= $quiz->pass_mark;

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'score' => $score,
            'percentage' => $percentage,
            'passed' => $passed,
            'attempted_at' => now(),
        ]);

        return redirect()->route('student.quizzes.result', [$quiz, $attempt]);
    }

    /** Show the quiz result screen */
    public function result(Quiz $quiz, QuizAttempt $attempt)
    {
        // Ensure the attempt belongs to the logged-in user
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        $quiz->load('questions.options');

        return view('student.quizzes.result', compact('quiz', 'attempt'));
    }

    /** Ensure the student is enrolled in the quiz's course */
    private function authorizeAccess(Quiz $quiz): void
    {
        $courseId = $quiz->unit->module->course_id;
        $enrolled = Auth::user()->enrolledCourses()
            ->where('courses.id', $courseId)->exists();
        if (!$enrolled) {
            abort(403, 'You are not enrolled in this course.');
        }
    }
}
