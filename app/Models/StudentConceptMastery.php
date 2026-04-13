<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentConceptMastery extends Model
{
    protected $fillable = ['student_id', 'micro_topic_id', 'mastery_percentage', 'total_attempts'];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function microTopic() { return $this->belongsTo(MicroTopic::class); }
}
