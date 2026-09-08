<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SparePart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                $search = $request->string('search');
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('manufacturer', 'like', "%{$search}%");
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
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can add spare parts.');
        }

        $validated = $request->validate([
            'part_number' => ['required', 'string', 'max:100', 'unique:spare_parts,part_number'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $part = SparePart::create($validated);

        ActivityLog::record(
            $request->user(),
            'spare_part.created',
            "Added spare part: {$part->name} [{$part->part_number}] (Stock: {$part->stock_quantity})",
            $part
        );

        return back()->with('success', "Spare part '{$part->name}' registered successfully.");
    }

    /**
     * Update an existing spare part or replenish stock.
     */
    public function update(Request $request, SparePart $sparePart): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'part_number' => ['required', 'string', 'max:100', Rule::unique('spare_parts', 'part_number')->ignore($sparePart->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $oldStock = $sparePart->stock_quantity;
        $sparePart->update($validated);

        ActivityLog::record(
            $user,
            'spare_part.updated',
            "Updated spare part: {$sparePart->name} [{$sparePart->part_number}] (Stock: {$oldStock} -> {$sparePart->stock_quantity})",
            $sparePart
        );

        return back()->with('success', "Spare part '{$sparePart->name}' updated successfully.");
    }

    /**
     * Delete an unused spare part from inventory (Admin only).
     */
    public function destroy(Request $request, SparePart $sparePart): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can delete spare parts.');
        }

        $usedCount = $sparePart->issues()->count();
        if ($usedCount > 0) {
            return back()->with('error', "Cannot delete spare part '{$sparePart->name}' because it is linked to {$usedCount} historical repair ticket(s).");
        }

        $name = $sparePart->name;
        $partNumber = $sparePart->part_number;

        ActivityLog::record(
            $request->user(),
            'spare_part.deleted',
            "Deleted spare part: {$name} [{$partNumber}]"
        );

        $sparePart->delete();

        return back()->with('success', "Spare part '{$name}' deleted successfully.");
    }
}
