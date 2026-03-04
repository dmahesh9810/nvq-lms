<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option_id',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /** The attempt this answer belongs to */
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class , 'attempt_id');
    }

    /** The question this answer is for */
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class , 'question_id');
    }

    /** The option the student selected */
    public function selectedOption()
    {
        return $this->belongsTo(QuizOption::class , 'selected_option_id');
    }
}
