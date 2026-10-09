<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property int|null $causer_id
 * @property string $event_type
 * @property string $description
 * @property array<string, mixed>|null $properties
 * @property Carbon|null $created_at
 * @property-read User|null $causer
 */
#[Fillable(['subject_type', 'subject_id', 'causer_id', 'event_type', 'description', 'properties', 'created_at'])]
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
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
     *
     * @return BelongsTo<User, $this>
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Helper to quickly record an activity.
     *
     * @param  array<string, mixed>|null  $properties
     */
    public static function record(?User $causer, string $eventType, string $description, ?Model $subject = null, ?array $properties = null): self
    {
        return self::create([
            'causer_id' => $causer?->id,
            'event_type' => $eventType,
            'description' => $description,
            'subject_type' => $subject instanceof Model ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
