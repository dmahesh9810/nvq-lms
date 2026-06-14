<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBadge extends Model
{
    protected $fillable = [
        'user_id', 'badge_key', 'badge_name',
        'badge_emoji', 'badge_description', 'earned_at',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Award a badge if not already earned. Returns the badge or null if already had it.
     */
    public static function award(int $userId, string $key, string $name, string $emoji, string $description = ''): ?self
    {
        // Check if already earned
        if (self::where('user_id', $userId)->where('badge_key', $key)->exists()) {
            return null;
        }

        return self::create([
            'user_id'          => $userId,
            'badge_key'        => $key,
            'badge_name'       => $name,
            'badge_emoji'      => $emoji,
            'badge_description'=> $description,
            'earned_at'        => now(),
        ]);
    }

    /**
     * Get all badges for a user, newest first.
     */
    public static function forUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)
            ->orderByDesc('earned_at')
            ->get();
    }
}
