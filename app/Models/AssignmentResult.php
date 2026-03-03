<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentResult extends Model
{
    use HasFactory;

    // NVQ competency status constants
    const COMPETENT         = 'competent';
    const NOT_YET_COMPETENT = 'not_yet_competent';

    protected $fillable = [
        'submission_id', 'assessor_id', 'competency_status',
        'marks', 'feedback', 'graded_at',
    ];

    protected $casts = [
        'graded_at' => 'datetime',
    ];

    /** The submission that was graded */
    public function submission()
    {
        return $this->belongsTo(AssignmentSubmission::class, 'submission_id');
    }

    /** The assessor who graded */
    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    /** Helper: badge color for competency status */
    public function competencyBadge(): string
    {
        return $this->competency_status === self::COMPETENT ? 'success' : 'danger';
    }

    /** Helper: short label (C / NYC) */
    public function competencyLabel(): string
    {
        return $this->competency_status === self::COMPETENT ? 'C' : 'NYC';
    }
}
