<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Enums\UserRole;
use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueStatusRequest;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\IssueReport;
use App\Models\SparePart;
use App\Models\User;
use App\Services\IssueWorkflowService;
use App\Support\FuzzySearch;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function __construct(private readonly IssueWorkflowService $issueWorkflow) {}

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
        $equipmentList = Equipment::with('department')->forUser($user)->active()->orderBy('name')->get();

        $query = IssueReport::with(['equipment', 'reporter', 'department', 'assignee'])
            ->forUser($user)
            ->when($request->filled('status') && $request->status !== 'all', function ($q) use ($request) {
                $q->where('progress_status', $request->status);
            })
            ->when($request->filled('priority') && $request->priority !== 'all', function ($q) use ($request) {
                $q->where('priority', $request->priority);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                FuzzySearch::apply($q, [
                    'title',
                    'description',
                    'equipment.name',
                    'equipment.asset_tag',
                    'reporter.name',
                    'assignee.name',
                    'department.name',
                ], (string) $request->string('search'));
            })
            ->latest();

        $issues = $query->paginate(15)->withQueryString();

        return view('pages.issues.index', compact('issues', 'progressStates', 'priorities', 'departments', 'equipmentList'));
    }

    /**
     * Store a newly created issue report.
     */
    public function store(StoreIssueRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        $equipment = Equipment::whereKey($validated['equipment_id'])->firstOrFail();

        // Check department scope
        if (! $user->isAdmin() && $equipment->department_id !== $user->department_id) {
            abort(403, 'You can only report issues on equipment in your assigned department.');
        }

        $issue = $this->issueWorkflow->report($validated, $user, $equipment);

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
    public function updateStatus(UpdateIssueStatusRequest $request, IssueReport $issue): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $issue->department_id !== $user->department_id) {
            abort(403, 'Unauthorized.');
        }

        $status = $this->issueWorkflow->transition($issue, $user, $request->validated());

        return back()->with('success', "Ticket status updated to '{$status->label()}'.");
    }

    /**
     * Delete an issue ticket (Admin only).
     */
    public function destroy(Request $request, IssueReport $issue): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can delete problem tickets.');
        }

        $id = $issue->id;

        $this->issueWorkflow->delete($issue, $request->user());

        return redirect()->route('issues.index')->with('success', "Ticket #{$id} deleted successfully.");
    }
}
