<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\IssueCommentController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // 📊 Operational Dashboard
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // 🏥 Track A: Equipment & Department Domain
    Route::get('equipment/export', [EquipmentController::class, 'exportCsv'])->name('equipment.export');
    Route::get('equipment', [EquipmentController::class, 'index'])->name('equipment.index');
    Route::post('equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::get('equipment/{equipment}/tag', [EquipmentController::class, 'printTag'])->name('equipment.tag');
    Route::get('equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');
    Route::patch('equipment/{equipment}/status', [EquipmentController::class, 'updateStatus'])->name('equipment.status');
    Route::post('equipment/{equipment}/archive', [EquipmentController::class, 'toggleArchive'])->name('equipment.archive');
    Route::post('equipment/{equipment}/attachments', [EquipmentController::class, 'uploadAttachment'])->name('equipment.attachments.store');
    Route::delete('equipment/{equipment}/attachments/{type}', [EquipmentController::class, 'deleteAttachment'])->name('equipment.attachments.destroy');
    Route::post('equipment/{equipment}/calibration', [EquipmentController::class, 'updateCalibration'])->name('equipment.calibration');
    Route::post('equipment/{equipment}/transfer', [EquipmentController::class, 'transferDepartment'])->name('equipment.transfer');

    Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');

    // 🛠️ Track B: Issue Reporting, Triage & Finite-State Lifecycle
    Route::get('issues', [IssueController::class, 'index'])->name('issues.index');
    Route::post('issues', [IssueController::class, 'store'])->name('issues.store');
    Route::get('issues/{issue}', [IssueController::class, 'show'])->name('issues.show');
    Route::patch('issues/{issue}/status', [IssueController::class, 'updateStatus'])->name('issues.status');

    // 💬 Issue Comments / Repair Work Log
    Route::post('issues/{issue}/comments', [IssueCommentController::class, 'store'])->name('issues.comments.store');
    Route::delete('issues/comments/{comment}', [IssueCommentController::class, 'destroy'])->name('issues.comments.destroy');

    // 📜 Activity & Audit Trail
    Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');

    // 📌 Clinical Sticky Notes Subsystem
    Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // 🩺 Operations & Health Diagnostics
    Route::get('health', HealthController::class)->name('health');
    Route::get('health/backup', [HealthController::class, 'download'])->name('health.backup.download');
});

require __DIR__.'/settings.php';
