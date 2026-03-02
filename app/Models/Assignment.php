<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = ['unit_id', 'title', 'description', 'due_date', 'max_marks', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'due_date'  => 'datetime',
    ];

    /** The unit this assignment belongs to */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /** All student submissions for this assignment */
    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /** Submission by a specific student */
    public function submissionByUser(int $userId): ?AssignmentSubmission
    {
        return $this->submissions()->where('user_id', $userId)->first();
    }
}
