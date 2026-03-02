<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['unit_id', 'title', 'description', 'pass_mark', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** The unit this quiz is in */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /** MCQ questions for this quiz */
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    /** All student attempts at this quiz */
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /** Get latest attempt for a specific student */
    public function lastAttemptByUser(int $userId): ?QuizAttempt
    {
        return $this->attempts()->where('user_id', $userId)->latest()->first();
    }

    /** Total marks available in the quiz */
    public function totalMarks(): int
    {
        return $this->questions()->sum('marks') ?: 0;
    }
}
