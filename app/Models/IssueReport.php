<?php

namespace App\Models;

use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['equipment_id', 'reporter_id', 'department_id', 'assigned_to_id', 'title', 'description', 'priority', 'progress_status', 'resolution_notes', 'resolved_at', 'closed_at'])]
class IssueReport extends Model
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
            'priority' => IssuePriority::class,
            'progress_status' => IssueProgress::class,
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'equipment_id' => 'integer',
            'department_id' => 'integer',
        ];
    }

    /**
     * Equipment item involved.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Staff reporter.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Department where issue originated.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Responsible technician or lead assigned.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * Repair work log comments.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(IssueComment::class);
    }

    /**
     * Spare parts used during repair.
     */
    public function spareParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class, 'issue_spare_parts')
            ->withPivot('quantity_used')
            ->withTimestamps();
    }

    /**
     * Scope for open issues.
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('progress_status', [IssueProgress::Resolved, IssueProgress::Closed]);
    }

    /**
     * Scope for user department access.
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }
}
