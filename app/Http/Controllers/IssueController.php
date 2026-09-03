<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\IssueReport;
use App\Models\SparePart;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IssueController extends Controller
{
    /**
     * Display a listing of issue tickets.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $progressStates = IssueProgress::cases();
        $priorities = IssuePriority::cases();
        $departments = Department::orderBy('name')->get();

        // Get available equipment for reporting modal
        $equipmentList = Equipment::forUser($user)->active()->orderBy('name')->get();

        $query = IssueReport::with(['equipment', 'reporter', 'department', 'assignee'])
            ->forUser($user)
            ->when($request->filled('status') && $request->status !== 'all', function ($q) use ($request) {
                $q->where('progress_status', $request->status);
            })
            ->when($request->filled('priority') && $request->priority !== 'all', function ($q) use ($request) {
                $q->where('priority', $request->priority);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('equipment', fn ($eq) => $eq->where('name', 'like', "%{$search}%")->orWhere('asset_tag', 'like', "%{$search}%"));
                });
            })
            ->latest();

        $issues = $query->paginate(15)->withQueryString();

        return view('pages.issues.index', compact('issues', 'progressStates', 'priorities', 'departments', 'equipmentList'));
    }

    /**
     * Store a newly created issue report.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'equipment_id' => ['required', 'exists:equipment,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'priority' => ['required', Rule::enum(IssuePriority::class)],
        ]);

        $equipment = Equipment::findOrFail($validated['equipment_id']);

        // Check department scope
        if (! $user->isAdmin() && $equipment->department_id !== $user->department_id) {
            abort(403, 'You can only report issues on equipment in your assigned department.');
        }

        $issue = IssueReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $user->id,
            'department_id' => $equipment->department_id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'progress_status' => IssueProgress::Reported,
        ]);

        // Auto-update equipment status to Under Review or Out for Repair if High/Critical
        if (in_array($issue->priority, [IssuePriority::High, IssuePriority::Critical])) {
            $equipment->update(['status' => EquipmentStatus::UnderReview]);
        }

        ActivityLog::record(
            $user,
            'issue.reported',
            "Reported problem on {$equipment->name} [{$equipment->asset_tag}]: '{$issue->title}'",
            $issue
        );

        return redirect()->route('issues.show', $issue)->with('success', 'Issue ticket reported successfully.');
    }

    /**
     * Display the specified issue details and triage terminal.
     */
    public function show(Request $request, IssueReport $issue): View
    {
        $user = $request->user();

        if (! $user->isAdmin() && $issue->department_id !== $user->department_id) {
            abort(403, 'Unauthorized issue access.');
        }

        $issue->load(['equipment.department', 'reporter', 'department', 'assignee', 'comments.author', 'spareParts']);
        $progressStates = IssueProgress::cases();
        $equipmentStatuses = EquipmentStatus::cases();
        $staffUsers = User::where('department_id', $issue->department_id)
            ->orWhere('role', UserRole::Admin->value)
            ->orderBy('name')
            ->get();
        $spareParts = SparePart::orderBy('name')->get();

        // Per-issue downtime (MTTR) if resolved
        $downtimeMinutes = null;
        if ($issue->resolved_at) {
            $downtimeMinutes = $issue->created_at->diffInMinutes($issue->resolved_at);
        }

        // Overdue flag: high/critical unresolved > 24 hours
        $isOverdue = in_array($issue->priority, [IssuePriority::High, IssuePriority::Critical])
            && ! in_array($issue->progress_status, [IssueProgress::Resolved, IssueProgress::Closed])
            && $issue->created_at->lessThan(Carbon::now()->subHours(24));

        return view('pages.issues.show', compact('issue', 'progressStates', 'equipmentStatuses', 'staffUsers', 'spareParts', 'downtimeMinutes', 'isOverdue'));
    }

    /**
     * Update issue progress status & assignment.
     */
    public function updateStatus(Request $request, IssueReport $issue): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $issue->department_id !== $user->department_id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'progress_status' => ['required', Rule::enum(IssueProgress::class)],
            'assigned_to_id' => ['nullable', Rule::exists('users', 'id')->where(function ($q) use ($issue) {
                $q->where('department_id', $issue->department_id)
                    ->orWhere('role', UserRole::Admin->value);
            })],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
            'equipment_status' => ['nullable', Rule::enum(EquipmentStatus::class)],
            'spare_part_ids' => ['nullable', 'array'],
            'spare_part_ids.*' => ['integer', 'exists:spare_parts,id'],
            'spare_part_quantities' => ['nullable', 'array'],
            'spare_part_quantities.*' => ['integer', 'min:1'],
        ]);

        $updateData = [
            'progress_status' => $validated['progress_status'],
        ];

        if (array_key_exists('assigned_to_id', $validated)) {
            $updateData['assigned_to_id'] = $validated['assigned_to_id'];
        }

        if (! empty($validated['resolution_notes'])) {
            $updateData['resolution_notes'] = $validated['resolution_notes'];
        }

        // Timestamp resolution
        if ($validated['progress_status'] === IssueProgress::Resolved->value && ! $issue->resolved_at) {
            $updateData['resolved_at'] = now();
        } elseif ($validated['progress_status'] === IssueProgress::Closed->value && ! $issue->closed_at) {
            $updateData['closed_at'] = now();
        }

        $issue->update($updateData);

        // Attach spare parts used, decrement stock, and update equipment gate atomically
        $partsUsed = DB::transaction(function () use ($request, $issue, $validated) {
            $partsUsed = $this->attachSpareParts($request, $issue);

            // Update equipment return-to-service gate if provided
            if (! empty($validated['equipment_status'])) {
                $issue->equipment->update(['status' => $validated['equipment_status']]);
            }

            return $partsUsed;
        });

        $statusLabel = $issue->progress_status->label();

        if ($partsUsed) {
            ActivityLog::record(
                $user,
                'issue.parts_used',
                "Logged {$partsUsed} part(s) used on issue #{$issue->id} ('{$issue->title}')",
                $issue
            );
        }

        ActivityLog::record(
            $user,
            'issue.status_changed',
            "Updated issue #{$issue->id} ('{$issue->title}') progress to '{$statusLabel}'",
            $issue
        );

        return back()->with('success', "Ticket status updated to '{$statusLabel}'.");
    }

    /**
     * Attach newly-submitted spare parts to an issue and decrement stock.
     *
     * Stock is withdrawn atomically and only for parts not already attached to
     * the issue, so repeated submissions never double-deduct. Parts that cannot
     * be fully supplied are skipped rather than driving stock negative.
     *
     * @return int number of part records attached
     */
    private function attachSpareParts(Request $request, IssueReport $issue): int
    {
        $parts = $request->input('spare_part_ids', []);
        $quantities = $request->input('spare_part_quantities', []);

        if (empty($parts)) {
            return 0;
        }

        // Parts already logged against this issue are ignored (idempotency).
        $alreadyAttached = $issue->spareParts()->pluck('spare_part_id')->all();

        $syncData = [];
        foreach ($parts as $index => $partId) {
            if (in_array((int) $partId, $alreadyAttached, true)) {
                continue;
            }

            $quantity = (int) ($quantities[$index] ?? 1);

            // Atomic conditional decrement — safe against concurrent overselling.
            $decremented = SparePart::where('id', $partId)
                ->where('stock_quantity', '>=', $quantity)
                ->decrement('stock_quantity', $quantity);

            if ($decremented === 1) {
                $syncData[$partId] = ['quantity_used' => $quantity];
            }
        }

        if (! empty($syncData)) {
            $issue->spareParts()->attach($syncData);
        }

        return count($syncData);
    }
}
