<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ClinicalNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $color
 * @property array<int, string>|null $tags
 * @property bool $is_pinned
 * @property int $author_id
 * @property int|null $department_id
 * @property int|null $equipment_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $author
 * @property-read Department|null $department
 * @property-read Equipment|null $equipment
 */
#[Fillable(['title', 'body', 'color', 'tags', 'is_pinned', 'author_id', 'department_id', 'equipment_id'])]
class ClinicalNote extends Model
{
    /** @use HasFactory<ClinicalNoteFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_pinned' => 'boolean',
            'department_id' => 'integer',
            'equipment_id' => 'integer',
        ];
    }

    /**
     * Author of the note.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Department associated with the note.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Equipment item this note is pinned to (optional).
     *
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * Scope for pinned notes.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }
}
