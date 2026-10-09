<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EquipmentStatus;
use App\Enums\IssueProgress;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\IssueReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Export the full hospital medical equipment inventory as a styled Excel (.xlsx) file.
     */
    public function exportEquipmentExcel(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Equipment Fleet');

        // Hospital Header Banner
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'MEDTRACK CLINICAL ASSET MANAGEMENT — EQUIPMENT INVENTORY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Metadata Subtitle
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'Generated on '.now()->format('F j, Y, g:i A').' | Facility: Main Hospital Facility');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('475569');
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Column Headers
        $headers = [
            'A3' => 'Asset Tag',
            'B3' => 'Device Nomenclature',
            'C3' => 'Manufacturer',
            'D3' => 'Model Number',
            'E3' => 'Serial Number',
            'F3' => 'Department Ward',
            'G3' => 'Room / Bay Location',
            'H3' => 'Operational State',
            'I3' => 'Next Calibration Due',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A3:I3';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(24);

        // Populate Equipment Records
        $equipment = Equipment::with('department')->orderBy('asset_tag')->get();
        $row = 4;

        foreach ($equipment as $item) {
            $calDue = $item->next_calibration_due ? $item->next_calibration_due->format('Y-m-d') : 'Unscheduled';

            $sheet->setCellValue("A{$row}", $item->asset_tag);
            $sheet->setCellValue("B{$row}", $item->name);
            $sheet->setCellValue("C{$row}", $item->manufacturer ?? 'Unknown');
            $sheet->setCellValue("D{$row}", $item->model_number ?? 'Standard');
            $sheet->setCellValue("E{$row}", $item->serial_number ?? 'N/A');
            $sheet->setCellValue("F{$row}", $item->department->name ?? 'Unassigned');
            $sheet->setCellValue("G{$row}", $item->location ?? 'General Ward');
            $sheet->setCellValue("H{$row}", $item->status->label());
            $sheet->setCellValue("I{$row}", $calDue);

            // Zebra Striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        $lastRow = max($row - 1, 4);

        // Apply Borders
        $sheet->getStyle("A3:I{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // Auto-fit columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'MedTrack_Equipment_Inventory_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export the Weekly Operational Digest as a structured Excel (.xlsx) workbook.
     *
     * @param  array<string, mixed>  $reportData
     */
    public function exportWeeklyReportExcel(array $reportData): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;

        // Sheet 1: Executive KPI Summary
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Executive Summary');

        $sheet1->mergeCells('A1:F1');
        $sheet1->setCellValue('A1', 'MEDTRACK WEEKLY CLINICAL OPERATIONS REPORT');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet1->getRowDimension(1)->setRowHeight(30);

        $sheet1->mergeCells('A2:F2');
        $sheet1->setCellValue('A2', "Reporting Period: {$reportData['startDate']} to {$reportData['endDate']} | Generated: ".now()->format('Y-m-d H:i'));
        $sheet1->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('475569');
        $sheet1->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet1->getRowDimension(2)->setRowHeight(20);

        // KPI Summary Block
        $sheet1->setCellValue('A4', 'Key Operational Metric');
        $sheet1->setCellValue('B4', 'Value');
        $sheet1->setCellValue('C4', 'Benchmark / Target');
        $sheet1->getStyle('A4:C4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('A4:C4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');

        $kpis = [
            ['Total Equipment Fleet', $reportData['totalEquipment'].' units', 'N/A'],
            ['Active In-Service Units', $reportData['inUseCount'].' units', '> 85%'],
            ['Under Repair / Triage', $reportData['underReviewCount'].' units', '< 10%'],
            ['Out of Service / Offline', $reportData['outOfServiceCount'].' units', '< 5%'],
            ['Overdue Calibrations', $reportData['overdueCalibrations'].' units', '0 units'],
            ['Calibrations Due Soon (≤30d)', $reportData['dueSoonCalibrations'].' units', 'Tracked'],
            ['Fleet Compliance Rate', $reportData['complianceRate'].'%', '100%'],
            ['New Fault Tickets (This Week)', $reportData['weeklyTicketsCount'].' tickets', 'Logged'],
            ['Resolved Fault Tickets (This Week)', $reportData['weeklyResolvedCount'].' tickets', 'Resolved'],
            ['Average MTTR (Resolution Time)', $reportData['avgMttrMinutes'].' minutes', '< 180 min'],
        ];

        $r = 5;
        foreach ($kpis as $kpi) {
            $sheet1->setCellValue("A{$r}", $kpi[0]);
            $sheet1->setCellValue("B{$r}", $kpi[1]);
            $sheet1->setCellValue("C{$r}", $kpi[2]);
            if ($r % 2 === 0) {
                $sheet1->getStyle("A{$r}:C{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }
            $r++;
        }
        $sheet1->getStyle('A4:C'.($r - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        foreach (range('A', 'C') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // Sheet 2: Department Breakdown
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Ward Breakdown');

        $sheet2->setCellValue('A1', 'Ward / Department');
        $sheet2->setCellValue('B1', 'Code');
        $sheet2->setCellValue('C1', 'Total Units');
        $sheet2->setCellValue('D1', 'Active Units');
        $sheet2->setCellValue('E1', 'Open Tickets');
        $sheet2->setCellValue('F1', 'Uptime Rate');
        $sheet2->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet2->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');

        $dr = 2;
        foreach ($reportData['departments'] as $dept) {
            $total = $dept->equipment_count;
            $active = $dept->active_equipment_count;
            $uptime = $total > 0 ? round(($active / $total) * 100, 1).'%' : 'N/A';

            $sheet2->setCellValue("A{$dr}", $dept->name);
            $sheet2->setCellValue("B{$dr}", $dept->code);
            $sheet2->setCellValue("C{$dr}", $total);
            $sheet2->setCellValue("D{$dr}", $active);
            $sheet2->setCellValue("E{$dr}", $dept->issues_count);
            $sheet2->setCellValue("F{$dr}", $uptime);

            if ($dr % 2 === 0) {
                $sheet2->getStyle("A{$dr}:F{$dr}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }
            $dr++;
        }
        $sheet2->getStyle('A1:F'.($dr - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        foreach (range('A', 'F') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'MedTrack_Weekly_Report_'.now()->format('Ymd').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export the hospital activity & audit log ledger as a styled Excel (.xlsx) file.
     */
    public function exportActivityExcel(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Ledger');

        // Hospital Header Banner
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'MEDTRACK CLINICAL ASSET MANAGEMENT — AUDIT LEDGER');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Metadata Subtitle
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Exported on '.now()->format('F j, Y, g:i A').' UTC | Full System Activity Trail');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('475569');
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Headers
        $headers = [
            'A3' => 'Timestamp (UTC)',
            'B3' => 'Event Type',
            'C3' => 'Initiator / Staff',
            'D3' => 'Activity Description',
            'E3' => 'Target Model / ID',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A3:E3';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(24);

        $activities = ActivityLog::with('causer')->latest('id')->get();
        $row = 4;

        foreach ($activities as $act) {
            $target = $act->subject_type ? class_basename($act->subject_type).' #'.$act->subject_id : 'System';

            $sheet->setCellValue("A{$row}", $act->created_at->format('Y-m-d H:i:s'));
            $sheet->setCellValue("B{$row}", $act->event_type);
            $sheet->setCellValue("C{$row}", $act->causer->name ?? 'System');
            $sheet->setCellValue("D{$row}", $act->description);
            $sheet->setCellValue("E{$row}", $target);

            $row++;
        }

        $lastRow = max(4, $row - 1);
        $sheet->getStyle("A3:E{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'MedTrack_Audit_Ledger_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Gather the operational data metrics for the Weekly Digest.
     *
     * Aggregations are pushed into SQL so no full model collections are
     * hydrated just to be counted or averaged in PHP.
     *
     * @return array{
     *     startDate: string,
     *     endDate: string,
     *     totalEquipment: int,
     *     inUseCount: int,
     *     underReviewCount: int,
     *     outOfServiceCount: int,
     *     overdueCalibrations: int,
     *     dueSoonCalibrations: int,
     *     complianceRate: float,
     *     weeklyTicketsCount: int,
     *     weeklyResolvedCount: int,
     *     avgMttrMinutes: float,
     *     departments: Collection<int, Department>,
     *     recentResolvedTickets: Collection<int, IssueReport>
     * }
     */
    public function getWeeklyMetrics(): array
    {
        $startDate = now()->subDays(7)->startOfDay();
        $endDate = now()->endOfDay();
        $today = now()->toDateString();
        $dueThreshold = now()->addDays(30)->toDateString();

        $totalEquipment = Equipment::count();
        $inUseCount = Equipment::where('status', EquipmentStatus::InUse)->count();
        $underReviewCount = Equipment::where('status', EquipmentStatus::UnderReview)->count();
        $outOfServiceCount = Equipment::where('status', EquipmentStatus::OutOfService)->count();

        $overdueCalibrations = Equipment::whereNotNull('next_calibration_due')
            ->where('next_calibration_due', '<', $today)
            ->count();

        $dueSoonCalibrations = Equipment::whereNotNull('next_calibration_due')
            ->whereBetween('next_calibration_due', [$today, $dueThreshold])
            ->count();

        $complianceRate = $totalEquipment > 0
            ? round((($totalEquipment - $overdueCalibrations) / $totalEquipment) * 100, 1)
            : 100.0;

        $weeklyTicketsCount = IssueReport::whereBetween('created_at', [$startDate, $endDate])->count();
        $weeklyResolvedCount = IssueReport::whereBetween('resolved_at', [$startDate, $endDate])->count();

        $mttrExpression = match (DB::connection()->getDriverName()) {
            'pgsql' => 'AVG(EXTRACT(EPOCH FROM (resolved_at - created_at)) / 60)',
            default => 'AVG((julianday(resolved_at) - julianday(created_at)) * 24 * 60)',
        };

        $avgMttrMinutes = round((float) (IssueReport::whereBetween('resolved_at', [$startDate, $endDate])
            ->selectRaw($mttrExpression.' AS avg_mttr_minutes')
            ->value('avg_mttr_minutes') ?? 0), 1);

        $departments = Department::withCount([
            'equipment',
            'activeEquipment as active_equipment_count',
            'issues' => static fn (Builder $query) => $query->whereIn('progress_status', IssueProgress::openValues()),
        ])->orderBy('name')->get();

        $recentResolvedTickets = IssueReport::with(['equipment', 'department', 'reporter', 'assignee'])
            ->whereNotNull('resolved_at')
            ->latest('resolved_at')
            ->limit(10)
            ->get();

        return [
            'startDate' => $startDate->format('M j, Y'),
            'endDate' => $endDate->format('M j, Y'),
            'totalEquipment' => $totalEquipment,
            'inUseCount' => $inUseCount,
            'underReviewCount' => $underReviewCount,
            'outOfServiceCount' => $outOfServiceCount,
            'overdueCalibrations' => $overdueCalibrations,
            'dueSoonCalibrations' => $dueSoonCalibrations,
            'complianceRate' => $complianceRate,
            'weeklyTicketsCount' => $weeklyTicketsCount,
            'weeklyResolvedCount' => $weeklyResolvedCount,
            'avgMttrMinutes' => $avgMttrMinutes,
            'departments' => $departments,
            'recentResolvedTickets' => $recentResolvedTickets,
        ];
    }
}
