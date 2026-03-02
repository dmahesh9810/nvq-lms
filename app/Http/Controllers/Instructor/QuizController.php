<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /** List all quizzes for this instructor's courses */
    public function index()
    {
        $quizzes = Quiz::whereHas('unit.module.course', function ($q) {
            $q->where('instructor_id', Auth::id());
        })->with('unit.module.course')->withCount('questions')->latest()->paginate(15);

        return view('instructor.quizzes.index', compact('quizzes'));
    }

    /** Show create form */
    public function create()
    {
        $units = Unit::whereHas('module.course', function ($q) {
            $q->where('instructor_id', Auth::id());
        })->with('module.course')->get();

        return view('instructor.quizzes.create', compact('units'));
    }

    /** Store a new quiz */
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pass_mark' => 'required|integer|min:1|max:100',
        ]);

        // Authorization: unit must belong to this instructor's course
        $unit = Unit::with('module.course')->findOrFail($data['unit_id']);
        if ($unit->module->course->instructor_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'You do not own the course this unit belongs to.');
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $quiz = Quiz::create($data);

        return redirect()->route('instructor.quizzes.questions', $quiz)
            ->with('success', 'Quiz created. Now add questions.');
    }

    /** Edit form */
    public function edit(Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $units = Unit::whereHas('module.course', function ($q) {
            $q->where('instructor_id', Auth::id());
        })->with('module.course')->get();

        return view('instructor.quizzes.edit', compact('quiz', 'units'));
    }

    /** Update quiz settings */
    public function update(Request $request, Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pass_mark' => 'required|integer|min:1|max:100',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $quiz->update($data);

        return redirect()->route('instructor.quizzes.index')
            ->with('success', 'Quiz updated.');
    }

    /** Delete quiz */
    public function destroy(Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $quiz->delete();

        return redirect()->route('instructor.quizzes.index')
            ->with('success', 'Quiz deleted.');
    }

    /** Manage MCQ questions for a quiz */
    public function questions(Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $quiz->load('questions.options');

        return view('instructor.quizzes.questions', compact('quiz'));
    }

    /** Store a new question with its options */
    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);

        $data = $request->validate([
            'question_text' => 'required|string',
            'marks' => 'required|integer|min:1',
            'order' => 'nullable|integer',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:500',
            'correct_option' => 'required|integer', // index of the correct option (0-based)
        ]);

        $question = $quiz->questions()->create([
            'question_text' => $data['question_text'],
            'marks' => $data['marks'],
            'order' => $data['order'] ?? $quiz->questions()->count(),
        ]);

        foreach ($data['options'] as $index => $optionText) {
            $question->options()->create([
                'option_text' => $optionText,
                'is_correct' => ($index == (int)$data['correct_option']),
            ]);
        }

        return redirect()->route('instructor.quizzes.questions', $quiz)
            ->with('success', 'Question added.');
    }

    /** Delete a specific question */
    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $this->authorizeQuiz($quiz);
        $question->delete();

        return redirect()->route('instructor.quizzes.questions', $quiz)
            ->with('success', 'Question deleted.');
    }

    private function authorizeQuiz(Quiz $quiz): void
    {
        if ($quiz->unit->module->course->instructor_id !== Auth::id()
        && !Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
