<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicroQuizQuestion extends Model
{
    protected $fillable = ['micro_topic_id', 'question_text'];

    public function options()
    {
        return $this->hasMany(MicroQuizOption::class);
    }
}
