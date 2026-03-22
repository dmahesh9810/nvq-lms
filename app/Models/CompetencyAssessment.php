<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unit_id',
        'assessor_id',
        'status',
        'remarks',
        'assessed_at',
    ];

    protected $casts = [
        'assessed_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** The student being assessed */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The unit this assessment is for */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /** The assessor who conducted the assessment */
    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }
}
