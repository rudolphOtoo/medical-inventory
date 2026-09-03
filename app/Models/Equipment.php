<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'model_number',
    'manufacturer',
    'asset_tag',
    'serial_number',
    'department_id',
    'location',
    'status',
    'description',
    'notes',
    'photo_path',
    'manual_path',
    'last_calibrated_at',
    'next_calibration_due',
    'is_archived',
    'created_by',
])]
class Equipment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'equipment';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EquipmentStatus::class,
            'is_archived' => 'boolean',
            'department_id' => 'integer',
            'last_calibrated_at' => 'date',
            'next_calibration_due' => 'date',
        ];
    }

    /**
     * Department owning this equipment.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * User who registered the equipment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Issue tickets logged for this equipment.
     */
    public function issues(): HasMany
    {
        return $this->hasMany(IssueReport::class)->latest();
    }

    /**
     * Sticky notes / clinical memos attached to this equipment.
     */
    public function clinicalNotes(): HasMany
    {
        return $this->hasMany(ClinicalNote::class)->latest();
    }

    /**
     * Get public URL for device photo.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    /**
     * Get public URL for user manual PDF.
     */
    public function getManualUrlAttribute(): ?string
    {
        return $this->manual_path ? Storage::disk('public')->url($this->manual_path) : null;
    }

    /**
     * Calculate current calibration status.
     *
     * @return array{key: string, label: string, variant: string, days_remaining: int|null}
     */
    public function calibrationStatus(): array
    {
        if (! $this->next_calibration_due) {
            return [
                'key' => 'uncalibrated',
                'label' => 'Unscheduled',
                'variant' => 'slate',
                'days_remaining' => null,
            ];
        }

        $now = now()->startOfDay();
        $due = $this->next_calibration_due->startOfDay();

        if ($due->isPast() && ! $due->isToday()) {
            return [
                'key' => 'overdue',
                'label' => 'Overdue ('.abs((int) $now->diffInDays($due, false)).'d)',
                'variant' => 'rose',
                'days_remaining' => (int) $now->diffInDays($due, false),
            ];
        }

        $days = (int) $now->diffInDays($due, false);

        if ($days <= 30) {
            return [
                'key' => 'due_soon',
                'label' => 'Due Soon ('.$days.'d)',
                'variant' => 'amber',
                'days_remaining' => $days,
            ];
        }

        return [
            'key' => 'certified',
            'label' => 'Certified',
            'variant' => 'emerald',
            'days_remaining' => $days,
        ];
    }

    /**
     * Check if calibration is past due.
     */
    public function isCalibrationOverdue(): bool
    {
        return $this->next_calibration_due && $this->next_calibration_due->startOfDay()->isPast() && ! $this->next_calibration_due->startOfDay()->isToday();
    }

    /**
     * Scope for active (unarchived) equipment.
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for department scoping.
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }

    /**
     * Scope for calibration status filtering.
     */
    public function scopeCalibrationStatus($query, ?string $status)
    {
        if (! $status || $status === 'all') {
            return $query;
        }

        $today = now()->toDateString();
        $threshold = now()->addDays(30)->toDateString();

        return match ($status) {
            'overdue' => $query->whereNotNull('next_calibration_due')->where('next_calibration_due', '<', $today),
            'due_soon' => $query->whereNotNull('next_calibration_due')->whereBetween('next_calibration_due', [$today, $threshold]),
            'certified' => $query->whereNotNull('next_calibration_due')->where('next_calibration_due', '>', $threshold),
            'uncalibrated' => $query->whereNull('next_calibration_due'),
            default => $query,
        };
    }
}
