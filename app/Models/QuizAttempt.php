<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id', 'user_id', 'score', 'percentage', 'passed', 'attempted_at',
    ];

    protected $casts = [
        'passed'       => 'boolean',
        'percentage'   => 'decimal:2',
        'attempted_at' => 'datetime',
    ];

    /** The quiz this attempt is for */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /** The student who attempted */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
