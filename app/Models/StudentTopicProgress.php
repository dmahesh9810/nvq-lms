<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTopicProgress extends Model
{
    protected $table = 'student_topic_progress';

    protected $fillable = [
        'user_id', 'micro_topic_id', 'is_completed',
        'xp_earned', 'attempts', 'completed_at',
        'mastery_score', 'correct_streak', 'mastered_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'mastered_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function user()        { return $this->belongsTo(User::class); }
    public function microTopic()  { return $this->belongsTo(MicroTopic::class); }

    // ── Mastery helpers ────────────────────────────────────────────────────
    public function getMasteryLevelAttribute(): string
    {
        return match(true) {
            $this->mastery_score >= 80 => 'mastered',   // 🟢
            $this->mastery_score >= 50 => 'learning',   // 🟡
            default                    => 'struggling', // 🔴
        };
    }

    // ── Core record method ────────────────────────────────────────────────
    /**
     * Record an attempt and update mastery score (0-100).
     * Mastery formula:
     *   - correct: +20 points (max 100), streak++
     *   - wrong:   -10 points (min 0), streak reset to 0
     *   - mastered_at set when score first reaches 80
     */
    public static function recordAttempt(int $userId, int $topicId, bool $passed, int $xp = 0): self
    {
        $progress = self::firstOrNew([
            'user_id'        => $userId,
            'micro_topic_id' => $topicId,
        ]);

        $progress->attempts = ($progress->attempts ?? 0) + 1;

        if ($passed) {
            $progress->correct_streak = ($progress->correct_streak ?? 0) + 1;
            $progress->mastery_score  = min(100, ($progress->mastery_score ?? 0) + 20);

            if (!$progress->is_completed) {
                $progress->is_completed = true;
                $progress->xp_earned    = $xp;
                $progress->completed_at = now();
            }
            // Set mastered_at when first hitting 80
            if ($progress->mastery_score >= 80 && !$progress->mastered_at) {
                $progress->mastered_at = now();
            }
        } else {
            $progress->correct_streak = 0;
            $progress->mastery_score  = max(0, ($progress->mastery_score ?? 0) - 10);
        }

        $progress->save();
        return $progress;
    }
}

