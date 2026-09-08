<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedTrack Weekly Operations Report - {{ $report['endDate'] }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            background: #ffffff;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
            font-family: monospace;
        }
        .kpi-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .kpi-row {
            display: table-row;
        }
        .kpi-cell {
            display: table-cell;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            width: 33.33%;
        }
        .kpi-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            font-family: monospace;
        }
        .kpi-val {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 10px;
            text-transform: uppercase;
            font-family: monospace;
            padding: 6px 8px;
            text-align: left;
        }
        table.data-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 11px;
        }
        table.data-table tr:nth-child(even) td {
            background: #f8fafc;
        }
        .signatures {
            margin-top: 40px;
            padding-top: 20px;
            display: table;
            width: 100%;
        }
        .sig-block {
            display: table-cell;
            width: 50%;
            padding-right: 20px;
        }
        .sig-line {
            border-top: 1px solid #0f172a;
            margin-top: 40px;
            padding-top: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0f172a; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-family: sans-serif; font-size: 12px;">
            Print / Save as PDF
        </button>
    </div>

    <!-- Header Banner -->
    <table class="header-table">
        <tr>
            <td>
                <h1 class="title">MedTrack Clinical Operations Report</h1>
                <div class="subtitle">Reporting Window: {{ $report['startDate'] }} &mdash; {{ $report['endDate'] }} | Facility: Main Hospital Center</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <span style="font-family: monospace; font-size: 11px; font-weight: bold; background: #0f172a; color: white; padding: 4px 8px; border-radius: 4px;">OFFICIAL REPORT</span>
            </td>
        </tr>
    </table>

    <!-- Executive Summary KPIs -->
    <div class="kpi-grid">
        <div class="kpi-row">
            <div class="kpi-cell">
                <div class="kpi-label">Fleet Compliance Rate</div>
                <div class="kpi-val" style="color: #059669;">{{ $report['complianceRate'] }}%</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Active Units in Service</div>
                <div class="kpi-val">{{ $report['inUseCount'] }} <span style="font-size: 11px; color: #64748b;">/ {{ $report['totalEquipment'] }}</span></div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Under Repair / Triage</div>
                <div class="kpi-val" style="color: #d97706;">{{ $report['underReviewCount'] }} units</div>
            </div>
        </div>
        <div class="kpi-row">
            <div class="kpi-cell">
                <div class="kpi-label">Overdue Calibrations</div>
                <div class="kpi-val" style="color: {{ $report['overdueCalibrations'] > 0 ? '#dc2626' : '#059669' }};">
                    {{ $report['overdueCalibrations'] }} units
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Fault Tickets Logged</div>
                <div class="kpi-val">{{ $report['weeklyTicketsCount'] }} <span style="font-size: 11px; color: #059669;">({{ $report['weeklyResolvedCount'] }} resolved)</span></div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Mean Time to Repair (MTTR)</div>
                <div class="kpi-val">{{ $report['avgMttrMinutes'] }} min</div>
            </div>
        </div>
    </div>

    <!-- Ward Breakdown Table -->
    <div class="section-title">1. Hospital Ward & Department Fleet Status</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Ward / Department</th>
                <th>Ward Code</th>
                <th style="text-align: center;">Total Units</th>
                <th style="text-align: center;">Active In Use</th>
                <th style="text-align: center;">Pending Tickets</th>
                <th style="text-align: right;">Uptime %</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report['departments'] as $dept)
                @php
                    $total = $dept->equipment_count;
                    $active = $dept->active_equipment_count;
                    $uptime = $total > 0 ? round(($active / $total) * 100, 1) : 100;
                @endphp
                <tr>
                    <td style="font-weight: 600;">{{ $dept->name }}</td>
                    <td style="font-family: monospace;">{{ $dept->code }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $total }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $active }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $dept->issues_count }}</td>
                    <td style="text-align: right; font-family: monospace; font-weight: bold; color: {{ $uptime >= 90 ? '#059669' : ($uptime >= 75 ? '#d97706' : '#dc2626') }};">
                        {{ $uptime }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resolved Tickets Section -->
    <div class="section-title">2. Completed Biomedical Maintenance Actions (Selected)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Ticket Title</th>
                <th>Medical Device</th>
                <th>Department</th>
                <th>Lead Technician</th>
                <th>Resolution Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['recentResolvedTickets'] as $ticket)
                <tr>
                    <td style="font-weight: 600;">{{ $ticket->title }}</td>
                    <td>{{ $ticket->equipment->name ?? 'N/A' }} <span style="font-family: monospace; color: #64748b;">[{{ $ticket->equipment->asset_tag ?? '' }}]</span></td>
                    <td>{{ $ticket->department->name ?? 'General' }}</td>
                    <td>{{ $ticket->assignee->name ?? 'Biomed Tech' }}</td>
                    <td style="color: #059669; font-weight: bold;">Verified Closed</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">No maintenance actions recorded in this window.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Official Signatures Block -->
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-line">Lead Biomedical Engineer Signature / Date</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Director of Clinical Operations Signature / Date</div>
        </div>
    </div>
</body>
</html>
