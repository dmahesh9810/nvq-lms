<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentQuizFailure extends Model
{
    protected $table = 'student_quiz_failures';

    protected $fillable = [
        'user_id',
        'micro_topic_id',
        'fail_count',
        'last_failed_at',
        'next_review_at',
    ];

    protected $casts = [
        'last_failed_at' => 'datetime',
        'next_review_at' => 'datetime',
    ];

    public function microTopic()
    {
        return $this->belongsTo(MicroTopic::class);
    }

    /**
     * Record a wrong answer and compute the next spaced review time.
     * Uses a simple interval: 1 fail → 1h, 2 fails → 4h, 3+ fails → 24h
     */
    public static function recordFailure(int $userId, int $topicId): self
    {
        $record = self::firstOrNew([
            'user_id'       => $userId,
            'micro_topic_id'=> $topicId,
        ]);

        $record->fail_count    = ($record->fail_count ?? 0) + 1;
        $record->last_failed_at = now();

        // Spaced repetition intervals
        // First fail → show immediately for revision
        // Subsequent → exponential back-off
        $intervalMinutes = match (true) {
            $record->fail_count <= 1 => 0,   // Immediate — show right away!
            $record->fail_count <= 2 => 60,  // 1 hour
            $record->fail_count <= 4 => 240, // 4 hours
            default                  => 1440,// 24 hours
        };

        $record->next_review_at = now()->addMinutes($intervalMinutes);
        $record->save();

        return $record;
    }

    /**
     * Get all topics due for review NOW for a given user.
     */
    public static function getDueForReview(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::with('microTopic')
            ->where('user_id', $userId)
            ->where('next_review_at', '<=', now())
            ->orderByDesc('fail_count')
            ->get();
    }
}
