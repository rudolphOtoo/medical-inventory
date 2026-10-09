<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSparePartRequest;
use App\Http\Requests\UpdateSparePartRequest;
use App\Models\ActivityLog;
use App\Models\SparePart;
use App\Support\FuzzySearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SparePartController extends Controller
{
    /**
     * Display a listing of replacement spare parts inventory.
     */
    public function index(Request $request): View
    {
        $spareParts = SparePart::withCount('issues')
            ->when($request->filled('search'), function ($q) use ($request) {
                FuzzySearch::apply($q, [
                    'name',
                    'part_number',
                    'manufacturer',
                    'description',
                ], (string) $request->string('search'));
            })
            ->when($request->filled('stock_status'), function ($q) use ($request) {
                if ($request->stock_status === 'low') {
                    $q->where('stock_quantity', '<=', 5);
                } elseif ($request->stock_status === 'out') {
                    $q->where('stock_quantity', 0);
                } elseif ($request->stock_status === 'in_stock') {
                    $q->where('stock_quantity', '>', 5);
                }
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $totalParts = SparePart::count();
        $totalQuantity = SparePart::sum('stock_quantity');
        $lowStockCount = SparePart::where('stock_quantity', '<=', 5)->count();

        return view('pages.spare-parts.index', compact('spareParts', 'totalParts', 'totalQuantity', 'lowStockCount'));
    }

    /**
     * Store a newly created spare part in inventory (Admin only).
     */
    public function store(StoreSparePartRequest $request): RedirectResponse
    {
        $this->authorize('create', SparePart::class);

        $validated = $request->validated();

        $part = DB::transaction(function () use ($request, $validated): SparePart {
            $part = SparePart::create($validated);

            ActivityLog::record(
                $request->user(),
                'spare_part.created',
                "Added spare part: {$part->name} [{$part->part_number}] (Stock: {$part->stock_quantity})",
                $part
            );

            return $part;
        });

        return back()->with('success', "Spare part '{$part->name}' registered successfully.");
    }

    /**
     * Update an existing spare part or replenish stock.
     */
    public function update(UpdateSparePartRequest $request, SparePart $sparePart): RedirectResponse
    {
        $this->authorize('update', $sparePart);

        $validated = $request->validated();

        $oldStock = $sparePart->stock_quantity;

        DB::transaction(function () use ($request, $sparePart, $validated, $oldStock): void {
            $sparePart->update($validated);

            ActivityLog::record(
                $request->user(),
                'spare_part.updated',
                "Updated spare part: {$sparePart->name} [{$sparePart->part_number}] (Stock: {$oldStock} -> {$sparePart->stock_quantity})",
                $sparePart
            );
        });

        return back()->with('success', "Spare part '{$sparePart->name}' updated successfully.");
    }

    /**
     * Delete an unused spare part from inventory (Admin only).
     */
    public function destroy(Request $request, SparePart $sparePart): RedirectResponse
    {
        $this->authorize('delete', $sparePart);

        $usedCount = $sparePart->issues()->count();
        if ($usedCount > 0) {
            return back()->with('error', "Cannot delete spare part '{$sparePart->name}' because it is linked to {$usedCount} historical repair ticket(s).");
        }

        $name = $sparePart->name;
        $partNumber = $sparePart->part_number;

        DB::transaction(function () use ($request, $sparePart, $name, $partNumber): void {
            ActivityLog::record(
                $request->user(),
                'spare_part.deleted',
                "Deleted spare part: {$name} [{$partNumber}]"
            );

            $sparePart->delete();
        });

        return back()->with('success', "Spare part '{$name}' deleted successfully.");
    }
}
