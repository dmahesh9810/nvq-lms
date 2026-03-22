<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'nvq_unit_code',
        'learning_outcomes',
        'performance_criteria',
        'assessment_criteria',
        'nvq_level',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** The module this unit belongs to */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /** Lessons within this unit */
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    /** Assignments within this unit */
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    /** Quizzes within this unit */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    /** NVQ Competency Assessments for this unit */
    public function competencyAssessments()
    {
        return $this->hasMany(CompetencyAssessment::class);
    }
}
