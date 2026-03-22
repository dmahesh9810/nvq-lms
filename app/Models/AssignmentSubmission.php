<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmission extends Model
{
    use HasFactory;

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_RESUBMITTED = 'resubmitted';
    public const STATUS_INSTRUCTOR_ASSESSED = 'instructor_assessed';
    public const STATUS_ASSESSOR_VERIFIED = 'assessor_verified';
    public const STATUS_ASSESSOR_REJECTED = 'assessor_rejected';

    protected $fillable = [
        'assignment_id', 'user_id', 'file_path', 'submitted_at', 'status',
        'instructor_id', 'instructor_review', 'instructor_reviewed_at', 'instructor_competency_status',
        'assessor_id', 'assessor_verification_note', 'verified_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'instructor_reviewed_at' => 'datetime',
        'verified_at' => 'datetime',
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

    public function isAssessorActioned(): bool
    {
        return in_array($this->status, [self::STATUS_ASSESSOR_VERIFIED, self::STATUS_ASSESSOR_REJECTED]);
    }

    public function isInstructorAssessed(): bool
    {
        return $this->status === self::STATUS_INSTRUCTOR_ASSESSED;
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
