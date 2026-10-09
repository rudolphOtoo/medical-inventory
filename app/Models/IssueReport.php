<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use Database\Factories\IssueReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $equipment_id
 * @property int $reporter_id
 * @property int $department_id
 * @property int|null $assigned_to_id
 * @property string $title
 * @property string $description
 * @property IssuePriority $priority
 * @property IssueProgress $progress_status
 * @property string|null $resolution_notes
 * @property Carbon|null $resolved_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Equipment|null $equipment
 * @property-read User|null $reporter
 * @property-read Department|null $department
 * @property-read User|null $assignee
 * @property-read Collection<int, IssueComment> $comments
 * @property-read Collection<int, SparePart> $spareParts
 */
#[Fillable(['equipment_id', 'reporter_id', 'department_id', 'assigned_to_id', 'title', 'description', 'priority', 'progress_status', 'resolution_notes', 'resolved_at', 'closed_at'])]
class IssueReport extends Model
{
    /** @use HasFactory<IssueReportFactory> */
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
     *
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Staff reporter.
     *
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Department where issue originated.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Responsible technician or lead assigned.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * Repair work log comments.
     *
     * @return HasMany<IssueComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(IssueComment::class);
    }

    /**
     * Spare parts used during repair.
     *
     * @return BelongsToMany<SparePart, $this>
     */
    public function spareParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class, 'issue_spare_parts')
            ->withPivot('quantity_used')
            ->withTimestamps();
    }

    /**
     * Scope for open issues.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('progress_status', [IssueProgress::Resolved, IssueProgress::Closed]);
    }

    /**
     * Scope for user department access.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }
}
