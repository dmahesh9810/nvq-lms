<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicroTopic extends Model
{
    protected $fillable = ["lesson_id", "topic_name", "description", "concept_cards", "key_takeaway", "estimated_minutes"];
    
    protected $casts = [
        'concept_cards' => 'array',
        'is_practical'  => 'boolean',
    ];

    public function lesson() { 
        return $this->belongsTo(Lesson::class); 
    }
    
    public function quizQuestions() { 
        return $this->hasMany(QuizQuestion::class); 
    }
    
    public function criterias() { 
        return $this->hasMany(AssignmentCriteria::class, 'micro_topic_id'); 
    }
}
