<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'status',
    ];

    /**
     * Auto-generate slug from title on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** The instructor who owns this course */
    public function instructor()
    {
        return $this->belongsTo(User::class , 'instructor_id');
    }

    /** Modules within this course */
    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    /** Students enrolled in this course */
    public function enrolledStudents()
    {
        return $this->belongsToMany(
            User::class ,
            'student_enrollments',
            'course_id',
            'user_id'
        )->withPivot('status', 'enrolled_at')->withTimestamps();
    }

    /** All enrollment records */
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Count all lessons in this course across all modules/units */
    public function totalLessons(): int
    {
        return Lesson::whereHas('unit.module', function ($q) {
            $q->where('course_id', $this->id);
        })->where('is_active', true)->count();
    }

    /** Calculate progress percentage for a given student */
    public function progressForStudent(int $userId): int
    {
        $total = $this->totalLessons();
        if ($total === 0)
            return 0;

        $completed = LessonProgress::where('user_id', $userId)
            ->whereHas('lesson.unit.module', function ($q) {
            $q->where('course_id', $this->id);
        })->count();

        return (int)round(($completed / $total) * 100);
    }
}
