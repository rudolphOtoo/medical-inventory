<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'floor', 'contact_number', 'head_of_department'])]
class Department extends Model
{
    use HasFactory;

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
