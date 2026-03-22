<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmission extends Model
{
    use HasFactory;

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_RESUBMITTED = 'resubmitted';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_ASSESSED = 'assessed';

    protected $fillable = [
        'assignment_id', 'user_id', 'file_path', 'submitted_at', 'status',
        'instructor_id', 'instructor_review', 'instructor_reviewed_at',
        'assessor_id',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'instructor_reviewed_at' => 'datetime',
    ];

    /** The assignment this submission belongs to */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /** The student who submitted */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The assessor result / grading record */
    public function result()
    {
        return $this->hasOne(AssignmentResult::class, 'submission_id');
    }

    public function isAssessed(): bool
    {
        return $this->result()->exists() && $this->status === self::STATUS_ASSESSED;
    }

    public function isReviewed(): bool
    {
        return $this->status === self::STATUS_REVIEWED;
    }

    /** The instructor who reviewed the assignment */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /** The assessor who graded the assignment */
    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }
}
