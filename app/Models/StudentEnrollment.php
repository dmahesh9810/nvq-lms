<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** The student who enrolled */
    public function student()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    /** The course enrolled in */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
