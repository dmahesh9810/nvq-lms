<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicroQuizOption extends Model
{
    protected $fillable = ['micro_quiz_question_id', 'option_text', 'is_correct'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];
}
