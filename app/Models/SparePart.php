<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'part_number', 'stock_quantity', 'unit_cost'])]
class SparePart extends Model
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
            'stock_quantity' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    /**
     * Issues that used this spare part.
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
