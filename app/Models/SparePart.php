<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SparePartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $part_number
 * @property int $stock_quantity
 * @property string $unit_cost
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, IssueReport> $issues
 * @property-read int|null $issues_count
 */
#[Fillable(['name', 'part_number', 'stock_quantity', 'unit_cost'])]
class SparePart extends Model
{
    /** @use HasFactory<SparePartFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    /**
     * Issues that used this spare part.
     *
     * @return BelongsToMany<IssueReport, $this>
     */
    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(IssueReport::class, 'issue_spare_parts')
            ->withPivot('quantity_used')
            ->withTimestamps();
    }

    /**
     * Determine if stock is running low.
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= 5;
    }
}
