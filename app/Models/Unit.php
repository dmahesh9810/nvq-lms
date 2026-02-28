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
}
