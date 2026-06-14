<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GamificationEngine;
use App\Services\IQBotService;
use App\Services\BadgeEngine;
use App\Models\StudentTopicProgress;
use App\Models\StudentQuizFailure;
use App\Models\StudentBadge;

class GamificationController extends Controller
{
    private GamificationEngine $engine;
    private BadgeEngine $badges;

    public function __construct(GamificationEngine $engine)
    {
        $this->engine = $engine;
        $this->badges = new BadgeEngine();
    }

    public function status(Request $request)
    {
        $stats     = $this->engine->getStats($request->user()->id);
        $levelInfo = GamificationEngine::getLevel($stats->total_xp);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'xp'                   => $stats->total_xp,
                'hearts'               => $stats->hearts,
                'current_streak'       => $stats->current_streak,
                'longest_streak'       => $stats->longest_streak,
                // Phase 7B: Level info
                'level'                => $levelInfo['level'],
                'level_title'          => $levelInfo['title'],
                'level_emoji'          => $levelInfo['emoji'],
                'level_perk'           => $levelInfo['perk'],
                'xp_to_next'           => $levelInfo['xp_to_next'],
                'level_progress'       => $levelInfo['progress_pct'],
                'is_max_level'         => $levelInfo['is_max_level'],
                // Phase 7C: Streak Shield
                'streak_shield_active' => (bool) ($stats->streak_shield_active ?? false),
                // Phase 7D: Daily Goal
                'daily_goal'           => $stats->daily_goal ?? 3,
                'daily_nodes_today'    => $stats->daily_nodes_today ?? 0,
            ]
        ]);
    }

    public function attemptMicroTopic(Request $request, $id)
    {
        $validated = $request->validate([
            'is_correct' => 'required|boolean',
            'concept_id' => 'required|integer'
        ]);

        $userId = $request->user()->id;
        $earnedXp = 0;
        $heartDeducted = false;

        if ($validated['is_correct']) {
            $earnedXp = 10;
            $this->engine->awardXP($userId, $earnedXp);
            StudentTopicProgress::recordAttempt($userId, (int) $id, true, $earnedXp);
            // Phase 7D: track daily nodes progress
            $dailyGoalAchieved = $this->engine->incrementDailyNodes($userId);
        } else {
            $this->engine->deductHeart($userId);
            $heartDeducted     = true;
            $dailyGoalAchieved = false;
            StudentTopicProgress::recordAttempt($userId, (int) $id, false, 0);
            StudentQuizFailure::recordFailure($userId, (int) $id);
        }

        // ── Phase 7A: run badge checks ──────────────────────────────
        $newBadges = $this->badges->check($userId, (int) $id, $validated['is_correct']);

        $progress = StudentTopicProgress::where([
            'user_id'        => $userId,
            'micro_topic_id' => (int) $id,
        ])->first();

        return response()->json([
            'status'  => 'success',
            'message' => 'Attempt registered',
            'data'    => [
                'xp_earned'           => $earnedXp,
                'heart_deducted'      => $heartDeducted,
                'mastery_score'       => $progress?->mastery_score ?? 0,
                'mastery_level'       => $progress?->mastery_level ?? 'struggling',
                'correct_streak'      => $progress?->correct_streak ?? 0,
                'is_mastered'         => ($progress?->mastery_score ?? 0) >= 80,
                'new_badges'          => $newBadges,
                'daily_goal_achieved' => $dailyGoalAchieved ?? false,  // Phase 7D
                'current_stats'       => $this->engine->getStats($userId),
            ]
        ]);
    }

    // ── Phase 7D: Set daily goal ───────────────────────────────────────────
    public function setDailyGoal(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'goal' => 'required|integer|in:1,3,5',
        ]);
        $this->engine->setDailyGoal($request->user()->id, $validated['goal']);
        return response()->json(['status' => 'success', 'message' => 'Daily goal updated']);
    }

    // ── Badges list ────────────────────────────────────────────────────────
    public function getBadges(Request $request): \Illuminate\Http\JsonResponse
    {
        $badges = StudentBadge::forUser($request->user()->id);
        return response()->json([
            'status' => 'success',
            'data'   => $badges->map(fn($b) => [
                'key'         => $b->badge_key,
                'name'        => $b->badge_name,
                'emoji'       => $b->badge_emoji,
                'description' => $b->badge_description,
                'earned_at'   => $b->earned_at->toDateTimeString(),
            ]),
        ]);
    }

    // ── Mastery summary for all nodes ──────────────────────────────────────
    public function getNodeMastery(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId   = $request->user()->id;
        $progress = StudentTopicProgress::where('user_id', $userId)->get();

        $summary = $progress->map(fn($p) => [
            'micro_topic_id' => $p->micro_topic_id,
            'mastery_score'  => $p->mastery_score,
            'mastery_level'  => $p->mastery_level,
            'correct_streak' => $p->correct_streak,
            'is_mastered'    => $p->mastery_score >= 80,
            'mastered_at'    => $p->mastered_at?->toDateTimeString(),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $summary,
        ]);
    }

    public function getMicroTopic($id)
    {
        $topic = \App\Models\MicroTopic::findOrFail($id);
        $questions = \App\Models\MicroQuizQuestion::with('options')->where('micro_topic_id', $id)->get();

        $formattedQuestions = $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question' => $q->question_text,
                'options' => $q->options->pluck('option_text'),
                'answer' => $q->options->where('is_correct', true)->first()->option_text ?? '',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'id'                  => $topic->id,
                'title'               => $topic->title,
                'content_html'        => $topic->content_html,
                'video_url'           => $topic->video_url,
                'is_practical'        => (bool) $topic->is_practical,
                'grading_rules'       => $topic->grading_rules ? json_decode($topic->grading_rules) : null,
                // Phase 9: Rich Lesson Flow
                'concept_cards'       => $topic->concept_cards ?? [],
                'key_takeaway'        => $topic->key_takeaway,
                'estimated_minutes'   => $topic->estimated_minutes ?? 5,
                'questions'           => $formattedQuestions
            ]
        ]);
    }

    /**
     * Automated Practical Grader - Student uploads a ZIP file and the
     * system autonomously validates it against the grading_rules.
     */
    public function attemptPractical(Request $request, $topicId)
    {
        $request->validate([
            'practical_file' => 'required|file|mimes:zip|max:10240'
        ]);

        $userId = $request->user()->id;
        $topic = \App\Models\MicroTopic::findOrFail($topicId);

        if (!$topic->is_practical) {
            return response()->json([
                'status' => 'error',
                'message' => 'This topic is not a practical assessment.'
            ], 400);
        }

        $file = $request->file('practical_file');
        $rules = $topic->grading_rules ? json_decode($topic->grading_rules, true) : [];

        $grader = new \App\Services\AutomatedGraderService();
        $result = $grader->gradePracticalUpload($file, $rules);

        if ($result['success']) {
            // Auto-award 50 XP for passing a practical
            $this->engine->awardXP($userId, 50);
            // ✅ Save persistent completion record
            StudentTopicProgress::recordAttempt($userId, (int) $topicId, true, 50);
            $stats = $this->engine->getStats($userId);

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => [
                    'xp_earned' => 50,
                    'heart_deducted' => false,
                    'current_stats' => $stats,
                ]
            ]);
        } else {
            // Deduct a heart on failure, just like a quiz
            $this->engine->deductHeart($userId);
            $stats = $this->engine->getStats($userId);

            return response()->json([
                'status' => 'failed',
                'message' => $result['message'],
                'data' => [
                    'xp_earned' => 0,
                    'heart_deducted' => true,
                    'current_stats' => $stats,
                ]
            ]);
        }
    }

    // ── Spaced Repetition: Topics due for review ───────────────────────────
    public function getSpacedRevision(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = $request->user()->id;

        $dueTopics = StudentQuizFailure::getDueForReview($userId);

        $items = $dueTopics->map(function ($failure) {
            return [
                'micro_topic_id' => $failure->micro_topic_id,
                'title'          => $failure->microTopic->title ?? 'Unknown Topic',
                'fail_count'     => $failure->fail_count,
                'last_failed_at' => $failure->last_failed_at?->diffForHumans(),
                'is_practical'   => (bool) ($failure->microTopic->is_practical ?? false),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'due_count' => $items->count(),
                'topics'    => $items,
            ],
        ]);
    }

    // ── IQ-Bot: Explain a wrong answer via Gemini AI ───────────────────────
    public function explainAnswer(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'question'       => 'required|string',
            'wrong_answer'   => 'required|string',
            'correct_answer' => 'required|string',
            'topic_title'    => 'required|string',
        ]);

        $bot         = new IQBotService();
        $explanation = $bot->explainWrongAnswer(
            $validated['question'],
            $validated['wrong_answer'],
            $validated['correct_answer'],
            $validated['topic_title'],
        );

        return response()->json([
            'status' => 'success',
            'data'   => [
                'explanation' => $explanation,
            ],
        ]);
    }

    // ── Phase 11: Global Leaderboard ───────────────────────────────────────
    public function leaderboard(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = $request->user()->id;

        // Fetch top 50 users globally based on XP
        $topUsers = \App\Models\StudentGamificationStat::with('user:id,name')
            ->orderBy('xp', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($stat, $index) {
                return [
                    'rank'        => $index + 1,
                    'user_id'     => $stat->user_id,
                    'name'        => $stat->user->name ?? 'Unknown Student',
                    'xp'          => $stat->xp,
                    'level'       => $stat->level,
                    'level_title' => $stat->level_title,
                ];
            });

        // Find the current user's rank if not in the top 50
        $currentUserRank = null;
        if (!$topUsers->contains('user_id', $userId)) {
            $myStat = \App\Models\StudentGamificationStat::where('user_id', $userId)->first();
            if ($myStat) {
                $rank = \App\Models\StudentGamificationStat::where('xp', '>', $myStat->xp)->count() + 1;
                $currentUserRank = [
                    'rank'        => $rank,
                    'user_id'     => $myStat->user_id,
                    'name'        => $request->user()->name,
                    'xp'          => $myStat->xp,
                    'level'       => $myStat->level,
                    'level_title' => $myStat->level_title,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'leaderboard'  => $topUsers,
                'current_user' => $currentUserRank,
            ],
        ]);
    }
}
