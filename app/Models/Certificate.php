<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'certificate_number',
        'issued_at',
        'status',
        'nvq_level',
        'assessor_id',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    /** The student who earned this certificate */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The course this certificate is for */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /** The assessor who approved this certificate */
    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Generate a unique certificate number in the format IQB-YYYY-XXXXXX.
     * Re-generates if a collision is found (extremely rare).
     */
    public static function generateNumber(): string
    {
        do {
            $number = 'IQB-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (static::where('certificate_number', $number)->exists());

        return $number;
    }

    /** Is this certificate currently active? */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
