<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepartmentStatus;
use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property DepartmentStatus $status
 * @property string|null $floor
 * @property string|null $contact_number
 * @property string|null $head_of_department
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $equipment_count
 * @property-read int|null $active_equipment_count
 * @property-read int|null $issues_count
 * @property-read int|null $staff_count
 */
#[Fillable(['name', 'code', 'description', 'status', 'floor', 'contact_number', 'head_of_department'])]
class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
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
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
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
     *
     * @return HasMany<Equipment, $this>
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    /**
     * Active operational equipment.
     *
     * @return HasMany<Equipment, $this>
     */
    public function activeEquipment(): HasMany
    {
        return $this->hasMany(Equipment::class)->where('is_archived', false);
    }

    /**
     * Issues originating from this department.
     *
     * @return HasMany<IssueReport, $this>
     */
    public function issues(): HasMany
    {
        return $this->hasMany(IssueReport::class);
    }

    /**
     * Department staff members.
     *
     * @return HasMany<User, $this>
     */
    public function staff(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
