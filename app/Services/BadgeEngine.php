<?php

namespace App\Services;

use App\Models\StudentBadge;
use App\Models\StudentTopicProgress;
use App\Models\StudentQuizFailure;
use App\Models\StudentGamificationStats;

/**
 * BadgeEngine — checks and awards badges after every quiz attempt.
 *
 * Badge definitions:
 *  🌱 first_step       – Complete first node
 *  🔥 on_fire          – 3-day streak
 *  💎 perfect_scorer   – 5 nodes at mastery 100%
 *  ⚡ speed_runner     – (future: complete node < 2 min)
 *  🧠 scholar          – 50 nodes completed
 *  🤖 bot_whisperer    – (future: used IQ-Bot 10x)
 *  🏆 module_master    – Complete all nodes in a module
 *  🛡️ streak_shield    – Earned at 7-day streak (enables shield perk)
 */
class BadgeEngine
{
    /**
     * Run all badge checks after an attempt. Returns newly awarded badges.
     *
     * @return array<array{key: string, name: string, emoji: string, description: string}>
     */
    public function check(int $userId, int $microTopicId, bool $passed): array
    {
        $awarded = [];

        if ($passed) {
            $awarded = array_merge($awarded,
                $this->checkFirstStep($userId),
                $this->checkScholar($userId),
                $this->checkPerfectScorer($userId),
            );
        }

        // Streak-based badges (check regardless of pass/fail)
        $awarded = array_merge($awarded,
            $this->checkStreakBadges($userId),
        );

        return $awarded;
    }

    // ── Individual badge checkers ──────────────────────────────────────────

    private function checkFirstStep(int $userId): array
    {
        $completedCount = StudentTopicProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->count();

        if ($completedCount >= 1) {
            return $this->tryAward($userId, 'first_step', 'First Step', '🌱',
                'Completed your very first lesson!');
        }
        return [];
    }

    private function checkScholar(int $userId): array
    {
        $completedCount = StudentTopicProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->count();

        if ($completedCount >= 50) {
            return $this->tryAward($userId, 'scholar', 'Scholar', '🧠',
                'Completed 50 nodes — you\'re a true scholar!');
        }
        return [];
    }

    private function checkPerfectScorer(int $userId): array
    {
        $perfectCount = StudentTopicProgress::where('user_id', $userId)
            ->where('mastery_score', 100)
            ->count();

        if ($perfectCount >= 5) {
            return $this->tryAward($userId, 'perfect_scorer', 'Perfect Scorer', '💎',
                'Achieved 100% mastery on 5 nodes!');
        }
        return [];
    }

    private function checkStreakBadges(int $userId): array
    {
        $awarded = [];
        $stats = StudentGamificationStats::where('user_id', $userId)->first();
        if (!$stats) return [];

        $streak = $stats->current_streak ?? 0;

        if ($streak >= 3) {
            $awarded = array_merge($awarded,
                $this->tryAward($userId, 'on_fire', 'On Fire', '🔥',
                    'Studied 3 days in a row!')
            );
        }

        if ($streak >= 7) {
            $awarded = array_merge($awarded,
                $this->tryAward($userId, 'streak_shield', 'Streak Shield', '🛡️',
                    '7-day streak! Your streak is now protected for 1 missed day.')
            );
        }

        if ($streak >= 30) {
            $awarded = array_merge($awarded,
                $this->tryAward($userId, 'dedicated', 'Dedicated Learner', '🏅',
                    '30-day streak — incredible dedication!')
            );
        }

        return $awarded;
    }

    // ── Helper ────────────────────────────────────────────────────────────
    private function tryAward(int $userId, string $key, string $name, string $emoji, string $desc): array
    {
        $badge = StudentBadge::award($userId, $key, $name, $emoji, $desc);
        if ($badge) {
            return [[
                'key'         => $badge->badge_key,
                'name'        => $badge->badge_name,
                'emoji'       => $badge->badge_emoji,
                'description' => $badge->badge_description,
            ]];
        }
        return [];
    }
}
