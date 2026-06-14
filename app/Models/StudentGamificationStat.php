<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGamificationStat extends Model
{
    protected $fillable = [
        'user_id',
        'total_xp',
        'current_streak',
        'longest_streak',
        'hearts',
        'last_activity_date',
    ];

    protected $casts = [
        'last_activity_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
