<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id', 'user_id', 'file_path', 'submitted_at', 'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
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

    /** Helper: has this submission been graded? */
    public function isGraded(): bool
    {
        return $this->result()->exists();
    }
}
