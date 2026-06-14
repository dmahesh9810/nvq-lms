<?php

namespace App\Services;

use App\Models\StudentGamificationStat;
use Carbon\Carbon;

class GamificationEngine
{
    /**
     * Award XP to a student.
     */
    public function awardXP(int $studentId, int $amount): void
    {
        $stat = StudentGamificationStat::firstOrCreate(
            ['user_id' => $studentId],
            ['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0, 'hearts' => 5]
        );

        $stat->total_xp += $amount;
        $stat->save();
        
        $this->updateStreak($studentId);
    }

    /**
     * Deduct a heart from a student on wrong attempt.
     */
    public function deductHeart(int $studentId): void
    {
        $stat = StudentGamificationStat::firstOrCreate(
            ['user_id' => $studentId],
            ['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0, 'hearts' => 5]
        );

        if ($stat->hearts > 0) {
            $stat->hearts -= 1;
            $stat->save();
        }
    }

    /**
     * Reward a heart (e.g. on practice mode).
     */
    public function rewardHeart(int $studentId, int $maxHearts = 5): void
    {
        $stat = StudentGamificationStat::firstOrCreate(
            ['user_id' => $studentId],
            ['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0, 'hearts' => 5]
        );

        if ($stat->hearts < $maxHearts) {
            $stat->hearts += 1;
            $stat->save();
        }
    }

    /**
     * Update the login/activity streak.
     * Phase 7C: if student misses a day but has shield → protect streak.
     */
    public function updateStreak(int $studentId): void
    {
        $stat = StudentGamificationStat::firstOrCreate(
            ['user_id' => $studentId],
            ['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0, 'hearts' => 5]
        );

        $today = Carbon::today();

        if (!$stat->last_activity_date) {
            $stat->current_streak = 1;
            $stat->longest_streak = 1;
        } else {
            $lastActivity = Carbon::parse($stat->last_activity_date)->startOfDay();
            $daysDiff     = $lastActivity->diffInDays($today);

            if ($daysDiff === 1) {
                // Consecutive day
                $stat->current_streak += 1;
                if ($stat->current_streak > $stat->longest_streak) {
                    $stat->longest_streak = $stat->current_streak;
                }
            } elseif ($daysDiff > 1) {
                // ── Phase 7C: Streak Shield ──────────────────────────
                if ($stat->streak_shield_active) {
                    // Shield absorbs the missed day, keep streak
                    $stat->streak_shield_active = false;
                    $stat->streak_shield_used_at = now();
                    // Don't reset streak, but don't increment either
                } else {
                    $stat->current_streak = 1;
                }
            }
            // diffInDays === 0: already updated today, do nothing.
        }

        // ── Phase 7C: Award shield when streak hits 7 ─────────────────
        if ($stat->current_streak >= 7 && !$stat->streak_shield_active
            && is_null($stat->streak_shield_used_at)) {
            $stat->streak_shield_active = true;
        }

        // ── Phase 7D: Reset daily goal counter at midnight ─────────────
        $lastReset = $stat->daily_goal_last_reset
            ? Carbon::parse($stat->daily_goal_last_reset)
            : null;
        if (!$lastReset || !$lastReset->isToday()) {
            $stat->daily_nodes_today     = 0;
            $stat->daily_goal_last_reset = $today;
        }

        $stat->last_activity_date = $today;
        $stat->save();
    }

    // ── Phase 7D: Daily Goal helpers ──────────────────────────────────────

    /**
     * Increment daily nodes count after a correct attempt.
     * Returns true if daily goal was just completed.
     */
    public function incrementDailyNodes(int $studentId): bool
    {
        $stat = StudentGamificationStat::firstOrCreate(
            ['user_id' => $studentId],
            ['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0, 'hearts' => 5]
        );

        // Reset if needed
        $lastReset = $stat->daily_goal_last_reset
            ? Carbon::parse($stat->daily_goal_last_reset)
            : null;
        if (!$lastReset || !$lastReset->isToday()) {
            $stat->daily_nodes_today     = 0;
            $stat->daily_goal_last_reset = Carbon::today();
        }

        $stat->daily_nodes_today += 1;
        $stat->save();

        // Return true when goal first reached
        return $stat->daily_nodes_today === $stat->daily_goal;
    }

    /**
     * Set the student's daily goal (1 = Easy, 3 = Medium, 5 = Hard).
     */
    public function setDailyGoal(int $studentId, int $goal): void
    {
        $stat = StudentGamificationStat::firstOrCreate(
            ['user_id' => $studentId],
            ['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0, 'hearts' => 5]
        );
        $stat->daily_goal = $goal;
        $stat->save();
    }


    /**
     * Get the student's gamification stats.
     */
    public function getStats(int $studentId): StudentGamificationStat
    {
        return StudentGamificationStat::firstOrCreate(
            ['user_id' => $studentId],
            ['total_xp' => 0, 'current_streak' => 0, 'longest_streak' => 0, 'hearts' => 5]
        );
    }

    // ── Phase 7B: Level System ─────────────────────────────────────────────

    /**
     * XP thresholds and titles for each level (1-7).
     */
    public static function levelDefinitions(): array
    {
        return [
            1 => ['title' => 'Beginner',     'min_xp' => 0,    'max_xp' => 99,   'emoji' => '🌱', 'perk' => null],
            2 => ['title' => 'Explorer',     'min_xp' => 100,  'max_xp' => 299,  'emoji' => '🔭', 'perk' => '+1 Heart bonus'],
            3 => ['title' => 'Learner',      'min_xp' => 300,  'max_xp' => 599,  'emoji' => '📚', 'perk' => 'Streak Shield unlocked'],
            4 => ['title' => 'Scholar',      'min_xp' => 600,  'max_xp' => 999,  'emoji' => '🎓', 'perk' => 'Speed boost XP x1.2'],
            5 => ['title' => 'Expert',       'min_xp' => 1000, 'max_xp' => 1999, 'emoji' => '⚡', 'perk' => 'IQ-Bot priority'],
            6 => ['title' => 'Master',       'min_xp' => 2000, 'max_xp' => 4999, 'emoji' => '🏆', 'perk' => 'Certificate unlock'],
            7 => ['title' => 'IQ-Champion',  'min_xp' => 5000, 'max_xp' => PHP_INT_MAX, 'emoji' => '👑', 'perk' => '🏆 Hall of Fame'],
        ];
    }

    /**
     * Get full level info for a given XP total.
     */
    public static function getLevel(int $totalXp): array
    {
        $levels = self::levelDefinitions();
        $currentLevel = 1;

        foreach ($levels as $lvl => $def) {
            if ($totalXp >= $def['min_xp']) {
                $currentLevel = $lvl;
            }
        }

        $def        = $levels[$currentLevel];
        $nextDef    = $levels[$currentLevel + 1] ?? null;
        $nextXp     = $nextDef['min_xp'] ?? $def['max_xp'];
        $xpInLevel  = $totalXp - $def['min_xp'];
        $xpNeeded   = $nextXp - $def['min_xp'];

        return [
            'level'          => $currentLevel,
            'title'          => $def['title'],
            'emoji'          => $def['emoji'],
            'perk'           => $def['perk'],
            'current_xp'     => $totalXp,
            'level_min_xp'   => $def['min_xp'],
            'next_level_xp'  => $nextXp,
            'xp_in_level'    => $xpInLevel,
            'xp_to_next'     => max(0, $xpNeeded - $xpInLevel),
            'progress_pct'   => $xpNeeded > 0 ? min(100, (int) round(($xpInLevel / $xpNeeded) * 100)) : 100,
            'is_max_level'   => $currentLevel === 7,
        ];
    }
}

