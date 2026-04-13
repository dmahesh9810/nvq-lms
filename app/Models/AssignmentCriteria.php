<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AssignmentCriteria extends Model
{
    protected $table = 'assignment_criterias';
    protected $fillable = ['assignment_id', 'micro_topic_id', 'criteria_name', 'max_marks'];

    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function microTopic() { return $this->belongsTo(MicroTopic::class); }
}
