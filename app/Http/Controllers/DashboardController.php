<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Enums\IssueProgress;
use App\Models\ClinicalNote;
use App\Models\Equipment;
use App\Models\IssueReport;
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
            'notes',
            'recentIssues'
        ));
    }
}
