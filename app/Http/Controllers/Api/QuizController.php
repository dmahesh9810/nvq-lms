<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizOption;

class QuizController extends Controller
{
    /**
     * Get quiz questions
     */
    public function show($id)
    {
        $quiz = Quiz::with('questions.options', 'questions.microTopic')->findOrFail($id);

        return response()->json([
            'data' => $quiz
        ]);
    }

    /**
     * Submit quiz answers
     */
    public function submit(Request $request, $id)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.selected_option_id' => 'required|exists:quiz_options,id',
            'answers.*.time_taken_seconds' => 'nullable|integer',
        ]);

        $quiz = Quiz::findOrFail($id);
        
        // Create an attempt
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $request->user()->id,
            'status' => 'completed',
            'score' => 0 // calculated later
        ]);

        $totalScore = 0;

        foreach ($request->answers as $ansData) {
            $option = QuizOption::find($ansData['selected_option_id']);
            $isCorrect = $option ? $option->is_correct : false;

            if ($isCorrect) {
                // Determine marks based on question
                $totalScore += $option->question->marks ?? 1;
            }

            QuizAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $ansData['question_id'],
                'selected_option_id' => $ansData['selected_option_id'],
                'is_correct' => $isCorrect,
                'time_taken_seconds' => $ansData['time_taken_seconds'] ?? null,
            ]);
        }

        $attempt->update(['score' => $totalScore]);

        return response()->json([
            'message' => 'Quiz submitted successfully',
            'score' => $totalScore
        ]);
    }
}
