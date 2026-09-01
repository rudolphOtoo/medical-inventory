<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['subject_type', 'subject_id', 'causer_id', 'event_type', 'description', 'properties', 'created_at'])]
class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Disable updated_at.
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * User who initiated the action.
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Helper to quickly record an activity.
     */
    public static function record(?User $causer, string $eventType, string $description, ?Model $subject = null, ?array $properties = null): self
    {
        return self::create([
            'causer_id' => $causer?->id,
            'event_type' => $eventType,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
