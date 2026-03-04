<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Services\CertificateService;
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

    /** Show the quiz start screen */
    public function showStart(Quiz $quiz)
    {
        $this->authorizeAccess($quiz);
        return view('student.quizzes.start', compact('quiz'));
    }

    /** Start a new quiz attempt */
    public function startQuiz(Quiz $quiz)
    {
        $this->authorizeAccess($quiz);

        $activeAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', Auth::id())
            ->whereNull('completed_at')
            ->first();

        if ($activeAttempt) {
            return redirect()->route('student.quizzes.attempt', [$quiz, $activeAttempt]);
        }

        $lastAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        if ($lastAttempt && $lastAttempt->result === 'PASS') {
            return redirect()->route('student.quizzes.result', [$quiz, $lastAttempt])
                ->with('info', 'You have already passed this quiz.');
        }

        // Create new attempt
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'score' => 0,
            'result' => null,
            'started_at' => now(),
        ]);

        return redirect()->route('student.quizzes.attempt', [$quiz, $attempt]);
    }

    /** Show the quiz questions to the student */
    public function showQuiz(Quiz $quiz, QuizAttempt $attempt)
    {
        $this->authorizeAccess($quiz);

        // Ensure attempt belongs to user
        if ($attempt->user_id !== Auth::id() || $attempt->quiz_id !== $quiz->id) {
            abort(403);
        }

        // If attempt already completed, redirect to result
        if ($attempt->completed_at) {
            return redirect()->route('student.quizzes.result', [$quiz, $attempt]);
        }

        $quiz->load('questions.options');

        return view('student.quizzes.attempt', compact('quiz', 'attempt'));
    }

    /** Submit the quiz answers and calculate score */
    public function submitQuiz(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        $this->authorizeAccess($quiz);

        // Ensure attempt belongs to user
        if ($attempt->user_id !== Auth::id() || $attempt->quiz_id !== $quiz->id) {
            abort(403);
        }

        // Prevent duplicate resubmission
        if ($attempt->completed_at) {
            return redirect()->route('student.quizzes.result', [$quiz, $attempt])
                ->with('error', 'Quiz already submitted.');
        }

        // Eager load everything needed for grading and certification check
        $quiz->loadMissing(['questions.options', 'unit.module.course']);

        $score = 0;
        $totalMarks = $quiz->questions->sum('marks');

        foreach ($quiz->questions as $question) {
            $selectedOptionId = $request->input("answers.{$question->id}");

            // Fix N+1: Query the collection instead of database relation
            $correctOption = $question->options->firstWhere('is_correct', true);

            $isCorrect = false;

            // Allow null/empty answers if student skips
            if ($selectedOptionId) {
                $isCorrect = $correctOption && ((int)$selectedOptionId === (int)$correctOption->id);
                if ($isCorrect) {
                    $score += $question->marks;
                }
            }

            // Save individual answer
            QuizAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'selected_option_id' => $selectedOptionId ?: null,
                'is_correct' => $isCorrect,
            ]);
        }

        // Fix Grading Math: Pass Mark is percentage-based
        $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100, 2) : 0;
        $result = $percentage >= $quiz->pass_mark ? 'PASS' : 'FAIL';

        $attempt->update([
            'score' => $score,
            'result' => $result,
            'completed_at' => now(),
        ]);

        $msg = 'Quiz submitted successfully.';

        // If they passed, check if they are now eligible for the course certificate
        if ($result === 'PASS') {
            $course = $quiz->unit->module->course;
            $certService = app(CertificateService::class);
            $certificate = $certService->checkAndIssueCertificate(Auth::user(), $course);

            if ($certificate) {
                $msg .= ' Congratulations! You have completed all course requirements and earned a certificate!';
            }
        }

        return redirect()->route('student.quizzes.result', [$quiz, $attempt])
            ->with('success', $msg);
    }

    /** Show the quiz result screen */
    public function showResult(Quiz $quiz, QuizAttempt $attempt)
    {
        // Ensure the attempt belongs to the logged-in user
        if ($attempt->user_id !== Auth::id() || $attempt->quiz_id !== $quiz->id) {
            abort(403);
        }

        $quiz->load('questions.options');
        $attempt->load('answers.selectedOption'); // load answers to show what they selected

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
