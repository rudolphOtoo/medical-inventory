<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportExportService $exportService
    ) {}

    /**
     * Display the Weekly Operational Digest Dashboard.
     */
    public function weekly(Request $request): View
    {
        $report = $this->exportService->getWeeklyMetrics();

        return view('pages.reports.weekly', compact('report'));
    }

    /**
     * Printable / PDF View of the Weekly Operational Digest.
     */
    public function printWeekly(Request $request): View
    {
        $report = $this->exportService->getWeeklyMetrics();

        return view('pages.reports.pdf', compact('report'));
    }

    /**
     * Download the Weekly Report or Equipment Inventory as an Excel (.xlsx) file.
     */
    public function download(Request $request): StreamedResponse
    {
        $type = $request->input('type', $request->route('type', 'weekly'));

        if ($type === 'equipment') {
            ActivityLog::record(
                $request->user(),
                'export.equipment_excel',
                'Exported hospital equipment inventory to Excel (.xlsx)'
            );

            return $this->exportService->exportEquipmentExcel();
        }

        ActivityLog::record(
            $request->user(),
            'export.weekly_report_excel',
            'Exported weekly hospital operational report to Excel (.xlsx)'
        );

        $reportData = $this->exportService->getWeeklyMetrics();

        return $this->exportService->exportWeeklyReportExcel($reportData);
    }
}
