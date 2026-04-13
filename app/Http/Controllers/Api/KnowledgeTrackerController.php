<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentConceptMastery;
use App\Models\QuizAnswer;
use App\Models\MicroTopic;
use Illuminate\Support\Facades\DB;

class KnowledgeTrackerController extends Controller
{
    /**
     * Internal function to calculate and update mastery scores
     * Usually called async or after a quiz submission.
     */
    public function syncMastery($userId)
    {
        // 1. Calculate Quiz Performance per Micro Topic
        $quizPerformance = QuizAnswer::select(
                'quiz_questions.micro_topic_id',
                DB::raw('COUNT(quiz_answers.id) as total_attempts'),
                DB::raw('SUM(CASE WHEN quiz_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers')
            )
            ->join('quiz_questions', 'quiz_answers.question_id', '=', 'quiz_questions.id')
            ->join('quiz_attempts', 'quiz_answers.attempt_id', '=', 'quiz_attempts.id')
            ->where('quiz_attempts.user_id', $userId)
            ->whereNotNull('quiz_questions.micro_topic_id')
            ->groupBy('quiz_questions.micro_topic_id')
            ->get();

        foreach ($quizPerformance as $perf) {
            $mastery = ($perf->correct_answers / $perf->total_attempts) * 100;
            
            StudentConceptMastery::updateOrCreate(
                ['student_id' => $userId, 'micro_topic_id' => $perf->micro_topic_id],
                [
                    'mastery_percentage' => $mastery,
                    'total_attempts' => $perf->total_attempts
                ]
            );
        }

        // Additional logic for AssignmentCriteria marking can be appended here similarly
    }

    /**
     * Return radar chart data for user's overall knowledge
     */
    public function radarChart(Request $request)
    {
        $user = $request->user();
        $this->syncMastery($user->id); // Sync live before visualizing

        $masteries = StudentConceptMastery::with('microTopic')
            ->where('student_id', $user->id)
            ->get();

        $labels = [];
        $data = [];

        foreach ($masteries as $mastery) {
            $labels[] = $mastery->microTopic->topic_name;
            $data[] = (float) $mastery->mastery_percentage;
        }

        return response()->json([
            'chart' => [
                'labels' => $labels,
                'data' => $data
            ]
        ]);
    }

    /**
     * Return weaknesses (< 50% mastery)
     */
    public function weaknesses(Request $request)
    {
        $user = $request->user();
        
        $weaknesses = StudentConceptMastery::with('microTopic')
            ->where('student_id', $user->id)
            ->where('mastery_percentage', '<', 50)
            ->where('total_attempts', '>=', 1) // Only count if they have tried
            ->get();

        return response()->json([
            'data' => $weaknesses
        ]);
    }

    /**
     * Return course recommendations based on weaknesses
     */
    public function recommendations(Request $request)
    {
        $user = $request->user();

        // Get lesson IDs associated with the weak microtopics
        $weakLessonIds = StudentConceptMastery::where('student_id', $user->id)
            ->where('mastery_percentage', '<', 50)
            ->join('micro_topics', 'student_concept_masteries.micro_topic_id', '=', 'micro_topics.id')
            ->pluck('micro_topics.lesson_id')
            ->unique();

        $recommendedLessons = \App\Models\Lesson::whereIn('id', $weakLessonIds)
            ->select('id', 'title', 'module_id')
            ->with('module.course:id,title')
            ->get();

        return response()->json([
            'data' => $recommendedLessons
        ]);
    }
}
