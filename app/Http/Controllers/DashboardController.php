<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Models\ClinicalNote;
use App\Models\Equipment;
use App\Models\IssueReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the MedTrack hospital operational dashboard with dynamic live metrics.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // 1. Dynamic Metric Counts
        $totalEquipment = Equipment::forUser($user)->active()->count();
        $inUseCount = Equipment::forUser($user)->active()->where('status', EquipmentStatus::InUse)->count();
        $underReviewCount = Equipment::forUser($user)->active()->whereIn('status', [EquipmentStatus::UnderReview, EquipmentStatus::OutForRepair])->count();
        $outOfServiceCount = Equipment::forUser($user)->active()->whereIn('status', [EquipmentStatus::OutOfService, EquipmentStatus::Lost])->count();

        // 2. Open Issues Count
        $openIssuesCount = IssueReport::forUser($user)
            ->whereNotIn('progress_status', [IssueProgress::Resolved, IssueProgress::Closed])
            ->count();

        // 2b. MTTR — average minutes from report to resolution (aggregated in SQL)
        $mttrMinutes = round((float) IssueReport::forUser($user)
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG((julianday(resolved_at) - julianday(created_at)) * 24 * 60) AS avg_mttr_minutes')
            ->value('avg_mttr_minutes') ?? 0);

        // 2c. Overdue high/critical issues open > 24 hours
        $overdueIssues = IssueReport::forUser($user)
            ->whereIn('priority', [IssuePriority::High, IssuePriority::Critical])
            ->whereNotIn('progress_status', [IssueProgress::Resolved, IssueProgress::Closed])
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->count();

        // 2d. 🧪 Calibration Status Alerts
        $today = now()->toDateString();
        $dueThreshold = now()->addDays(30)->toDateString();

        $overdueCalibrationCount = Equipment::forUser($user)
            ->active()
            ->whereNotNull('next_calibration_due')
            ->where('next_calibration_due', '<', $today)
            ->count();

        $dueSoonCalibrationCount = Equipment::forUser($user)
            ->active()
            ->whereNotNull('next_calibration_due')
            ->whereBetween('next_calibration_due', [$today, $dueThreshold])
            ->count();

        $certifiedCalibrationCount = Equipment::forUser($user)
            ->active()
            ->whereNotNull('next_calibration_due')
            ->where('next_calibration_due', '>', $dueThreshold)
            ->count();

        // 3. Clinical Sticky Notes (User Scoped)
        $notes = ClinicalNote::with('author')
            ->when(! $user->isAdmin(), function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('department_id')
                        ->orWhere('department_id', $user->department_id);
                });
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(12)
            ->get();

        // 4. Recent Issues Feed
        $recentIssues = IssueReport::with(['equipment', 'reporter', 'assignee'])
            ->forUser($user)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalEquipment',
            'inUseCount',
            'underReviewCount',
            'outOfServiceCount',
            'openIssuesCount',
            'mttrMinutes',
            'overdueIssues',
            'overdueCalibrationCount',
            'dueSoonCalibrationCount',
            'certifiedCalibrationCount',
            'notes',
            'recentIssues'
        ));
    }
}
