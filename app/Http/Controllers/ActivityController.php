<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ReportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller
{
    public function __construct(
        protected ReportExportService $exportService
    ) {}

    /**
     * Display the hospital activity and audit trail (Admin only).
     */
    public function index(Request $request): View
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Restricted to hospital administrators.');
        }

        $activities = ActivityLog::with('causer')
            ->when($request->filled('event_type') && $request->event_type !== 'all', function ($q) use ($request) {
                $q->where('event_type', $request->event_type);
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $eventTypes = ActivityLog::select('event_type')->distinct()->pluck('event_type');
        $totalLogsCount = ActivityLog::count();

        return view('pages.activity.index', compact('activities', 'eventTypes', 'totalLogsCount'));
    }

    /**
     * Export the full hospital activity log as an Excel (.xlsx) file.
     */
    public function export(Request $request): StreamedResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Restricted to hospital administrators.');
        }

        ActivityLog::record(
            $request->user(),
            'audit.exported',
            'Exported full hospital audit ledger to Excel (.xlsx)'
        );

        return $this->exportService->exportActivityExcel();
    }

    /**
     * Prune or clear the audit log ledger (Admin only).
     */
    public function prune(Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Restricted to hospital administrators.');
        }

        $mode = $request->input('mode', 'older_than_30');

        if ($mode === 'all') {
            $count = ActivityLog::count();
            ActivityLog::truncate();

            ActivityLog::record(
                $request->user(),
                'audit.cleared',
                "Cleared entire audit ledger ({$count} entries removed)."
            );

            return back()->with('success', "Audit ledger cleared successfully ({$count} records removed).");
        }

        $cutoff = now()->subDays(30);
        $deleted = ActivityLog::where('created_at', '<', $cutoff)->delete();

        ActivityLog::record(
            $request->user(),
            'audit.pruned',
            "Pruned {$deleted} audit log entries older than 30 days."
        );

        return back()->with('success', "Pruned {$deleted} audit log entries older than 30 days.");
    }
}
