<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** The student */
    public function student()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    /** The lesson that was completed */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
