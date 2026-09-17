<?php

namespace App\Models;

use App\Enums\DepartmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'description', 'status', 'floor', 'contact_number', 'head_of_department'])]
class Department extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DepartmentStatus::class,
        ];
    }

    /**
     * Scope the query to operational departments only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', DepartmentStatus::Active->value);
    }

    /**
     * Determine whether the department is currently operational.
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Equipment belonging to this department.
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    /**
     * Active operational equipment.
     */
    public function activeEquipment(): HasMany
    {
        return $this->hasMany(Equipment::class)->where('is_archived', false);
    }

    /**
     * Issues originating from this department.
     */
    public function issues(): HasMany
    {
        return $this->hasMany(IssueReport::class);
    }

    /**
     * Department staff members.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
