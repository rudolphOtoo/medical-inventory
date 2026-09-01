<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'model_number', 'manufacturer', 'asset_tag', 'serial_number', 'department_id', 'location', 'status', 'description', 'notes', 'is_archived', 'created_by'])]
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
}
