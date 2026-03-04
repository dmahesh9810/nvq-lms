<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id', 'user_id', 'score', 'result', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /** The quiz this attempt is for */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /** The student who attempted */
    public function student()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    /** The answers submitted for this attempt */
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class , 'attempt_id');
    }
}
