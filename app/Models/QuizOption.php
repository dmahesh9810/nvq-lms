<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizOption extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'option_text', 'is_correct'];

    protected $casts = ['is_correct' => 'boolean'];

    /** The question this option belongs to */
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
