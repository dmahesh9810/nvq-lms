<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'action',
        'target_id',
        'target_title',
        'payload',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'reviewed_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Resolve the target model class based on the type field.
     */
    public function targetModelClass(): string
    {
        return match ($this->type) {
            'course'  => Course::class,
            'module'  => Module::class,
            'unit'    => Unit::class,
            'lesson'  => Lesson::class,
            default   => throw new \InvalidArgumentException("Unknown change request type: {$this->type}"),
        };
    }

    /**
     * Fetch the live target model instance (returns null if deleted).
     */
    public function resolveTarget(): ?Model
    {
        $class = $this->targetModelClass();
        return $class::find($this->target_id);
    }

    /**
     * Human-readable label for the type.
     */
    public function typeLabel(): string
    {
        return ucfirst($this->type);
    }

    /**
     * Human-readable label for the action.
     */
    public function actionLabel(): string
    {
        return ucfirst($this->action);
    }
}
