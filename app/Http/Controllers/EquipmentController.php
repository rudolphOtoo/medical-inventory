<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EquipmentController extends Controller
{
    /**
     * Display a listing of medical equipment.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $statuses = EquipmentStatus::cases();
        $departments = Department::orderBy('name')->get();

        $query = Equipment::with(['department', 'issues' => fn ($q) => $q->open()])
            ->forUser($user)
            ->active()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('manufacturer', 'like', "%{$search}%")
                        ->orWhere('model_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status') && $request->status !== 'all', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('department_id') && $request->department_id !== 'all' && $user->isAdmin(), function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            })
            ->latest();

        $equipmentList = $query->paginate(15)->withQueryString();

        return view('pages.equipment.index', compact('equipmentList', 'statuses', 'departments'));
    }

    /**
     * Store a newly created equipment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'model_number' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'asset_tag' => ['required', 'string', 'max:50', 'unique:equipment,asset_tag'],
            'serial_number' => ['nullable', 'string', 'max:100', 'unique:equipment,serial_number'],
            'department_id' => ['required', 'exists:departments,id'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // If not admin, restrict to own department
        if (! $user->isAdmin()) {
            $validated['department_id'] = $user->department_id;
        }

        $validated['created_by'] = $user->id;

        $equipment = Equipment::create($validated);

        ActivityLog::record(
            $user,
            'equipment.created',
            "Registered medical device: {$equipment->name} [{$equipment->asset_tag}]",
            $equipment
        );

        return redirect()->route('equipment.show', $equipment)->with('success', "Equipment '{$equipment->name}' registered successfully.");
    }

    /**
     * Display the specified equipment details.
     */
    public function show(Request $request, Equipment $equipment): View
    {
        $user = $request->user();

        // Check department scope
        if (! $user->isAdmin() && $equipment->department_id !== $user->department_id) {
            abort(403, 'Unauthorized device access.');
        }

        $equipment->load(['department', 'creator', 'issues.reporter', 'issues.assignee', 'notes.author']);
        $statuses = EquipmentStatus::cases();

        return view('pages.equipment.show', compact('equipment', 'statuses'));
    }

    /**
     * Update equipment operational status.
     */
    public function updateStatus(Request $request, Equipment $equipment): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $equipment->department_id !== $user->department_id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
        ]);

        $oldStatus = $equipment->status->label();
        $equipment->update(['status' => $validated['status']]);
        $newStatus = $equipment->status->label();

        ActivityLog::record(
            $user,
            'equipment.status_changed',
            "Updated {$equipment->name} status from '{$oldStatus}' to '{$newStatus}'",
            $equipment
        );

        return back()->with('success', "Equipment status updated to '{$newStatus}'.");
    }

    /**
     * Toggle archive status.
     */
    public function toggleArchive(Request $request, Equipment $equipment): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can archive equipment.');
        }

        $equipment->update(['is_archived' => ! $equipment->is_archived]);

        $state = $equipment->is_archived ? 'Archived' : 'Restored';
        ActivityLog::record(
            $request->user(),
            'equipment.archived',
            "{$state} equipment: {$equipment->name} [{$equipment->asset_tag}]",
            $equipment
        );

        return redirect()->route('equipment.index')->with('success', "Equipment '{$equipment->name}' {$state}.");
    }

    /**
     * Export equipment inventory as CSV (Admin only).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $filename = 'medtrack-inventory-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Asset Tag', 'Name', 'Manufacturer', 'Model', 'Serial Number', 'Department', 'Location', 'Status', 'Registered At']);

            Equipment::with('department')->chunk(100, function ($items) use ($handle) {
                foreach ($items as $item) {
                    fputcsv($handle, [
                        $item->id,
                        $item->asset_tag,
                        $item->name,
                        $item->manufacturer,
                        $item->model_number,
                        $item->serial_number,
                        $item->department->name ?? 'N/A',
                        $item->location,
                        $item->status->label(),
                        $item->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
