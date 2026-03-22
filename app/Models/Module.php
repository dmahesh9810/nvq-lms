<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** The course this module belongs to */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /** Instructors specifically assigned to this module via pivot */
    public function assignedInstructors()
    {
        return $this->belongsToMany(User::class, 'module_user')
            ->withTimestamps();
    }

    /** Units within this module */
    public function units()
    {
        return $this->hasMany(Unit::class)->orderBy('order');
    }
}
