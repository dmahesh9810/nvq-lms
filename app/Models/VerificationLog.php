<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationLog extends Model
{
    protected $fillable = [
        'assessor_id',
        'instructor_id',
        'submission_id',
        'action',
        'note',
    ];

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function submission()
    {
        return $this->belongsTo(AssignmentSubmission::class, 'submission_id');
    }
}
