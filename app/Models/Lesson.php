<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'title',
        'description',
        'video_url',
        'pdf_path',
        'content',
        'type',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** The unit this lesson belongs to */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /** Progress records for this lesson */
    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Check if a given student has completed this lesson */
    public function isCompletedByUser(int $userId): bool
    {
        return $this->progress()
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->exists();
    }

    /**
     * Convert a YouTube watch URL to an embed URL.
     * e.g. https://youtube.com/watch?v=XYZ => https://www.youtube.com/embed/XYZ
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->video_url)
            return null;

        // Support both youtube.com/watch?v= and youtu.be/ formats
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $this->video_url, $matches);
        if (!empty($matches[1])) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        return $this->video_url;
    }
}
