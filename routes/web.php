<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\IssueCommentController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SparePartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // 📊 Operational Dashboard
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // 📈 Weekly Executive Reports & Data Portability Hub
    Route::get('reports/weekly', [ReportController::class, 'weekly'])->name('reports.weekly');
    Route::get('reports/print', [ReportController::class, 'printWeekly'])->name('reports.print');
    Route::get('reports/download', [ReportController::class, 'download'])->name('reports.download');

    // 🏥 Equipment Domain
    Route::get('equipment/export', [ReportController::class, 'download'])->name('equipment.export')->defaults('type', 'equipment');
    Route::get('equipment', [EquipmentController::class, 'index'])->name('equipment.index');
    Route::post('equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::get('equipment/{equipment}/tag', [EquipmentController::class, 'printTag'])->name('equipment.tag');
    Route::get('equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');
    Route::put('equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::delete('equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');
    Route::patch('equipment/{equipment}/status', [EquipmentController::class, 'updateStatus'])->name('equipment.status');
    Route::post('equipment/{equipment}/archive', [EquipmentController::class, 'toggleArchive'])->name('equipment.archive');
    Route::post('equipment/{equipment}/attachments', [EquipmentController::class, 'uploadAttachment'])->name('equipment.attachments.store');
    Route::delete('equipment/{equipment}/attachments/{type}', [EquipmentController::class, 'deleteAttachment'])->name('equipment.attachments.destroy');
    Route::post('equipment/{equipment}/calibration', [EquipmentController::class, 'updateCalibration'])->name('equipment.calibration');
    Route::post('equipment/{equipment}/transfer', [EquipmentController::class, 'transferDepartment'])->name('equipment.transfer');

    // 🏢 Department Domain (Full CRUD)
    Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // 🛠️ Repair & Fault Tickets (Full CRUD)
    Route::get('issues', [IssueController::class, 'index'])->name('issues.index');
    Route::post('issues', [IssueController::class, 'store'])->name('issues.store');
    Route::get('issues/{issue}', [IssueController::class, 'show'])->name('issues.show');
    Route::patch('issues/{issue}/status', [IssueController::class, 'updateStatus'])->name('issues.status');
    Route::delete('issues/{issue}', [IssueController::class, 'destroy'])->name('issues.destroy');

    // ⚙️ Spare Parts Inventory Catalog (Full CRUD)
    Route::get('spare-parts', [SparePartController::class, 'index'])->name('spare-parts.index');
    Route::post('spare-parts', [SparePartController::class, 'store'])->name('spare-parts.store');
    Route::put('spare-parts/{spare_part}', [SparePartController::class, 'update'])->name('spare-parts.update');
    Route::delete('spare-parts/{spare_part}', [SparePartController::class, 'destroy'])->name('spare-parts.destroy');

    // 💬 Issue Comments / Repair Work Log
    Route::post('issues/{issue}/comments', [IssueCommentController::class, 'store'])->name('issues.comments.store');
    Route::delete('issues/comments/{comment}', [IssueCommentController::class, 'destroy'])->name('issues.comments.destroy');

    // 📜 Activity & Audit Trail (Export & Prune)
    Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
    Route::get('activity/export', [ActivityController::class, 'export'])->name('activity.export');
    Route::post('activity/prune', [ActivityController::class, 'prune'])->name('activity.prune');

    // 📌 Clinical Sticky Notes Subsystem (Full CRUD)
    Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
    Route::put('notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::patch('notes/{note}/pin', [NoteController::class, 'togglePin'])->name('notes.pin');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // 🩺 Operations & Health Diagnostics
    Route::get('health', HealthController::class)->name('health');
    Route::post('health/backup', [HealthController::class, 'create'])->name('health.backup.create');
    Route::get('health/backup', [HealthController::class, 'download'])->name('health.backup.download');
});

require __DIR__.'/settings.php';
