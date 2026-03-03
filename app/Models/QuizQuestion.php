<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_id', 'question_text', 'marks', 'order'];

    /** The quiz this question belongs to */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /** Answer options for this MCQ */
    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id');
    }

    /** The correct option */
    public function correctOption()
    {
        return $this->options()->where('is_correct', true)->first();
    }
}
