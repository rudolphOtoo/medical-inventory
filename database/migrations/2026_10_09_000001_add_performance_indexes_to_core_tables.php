<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->index('status');
            $table->index('next_calibration_due');
            $table->index(['department_id', 'status']);
        });

        Schema::table('issue_reports', function (Blueprint $table) {
            $table->index('progress_status');
            $table->index('priority');
            $table->index('resolved_at');
            $table->index(['department_id', 'progress_status']);
            $table->index(['progress_status', 'created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('event_type');
            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::table('spare_parts', function (Blueprint $table) {
            $table->index('stock_quantity');
        });

        Schema::table('clinical_notes', function (Blueprint $table) {
            $table->index('is_pinned');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['next_calibration_due']);
            $table->dropIndex(['department_id', 'status']);
        });

        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropIndex(['progress_status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['resolved_at']);
            $table->dropIndex(['department_id', 'progress_status']);
            $table->dropIndex(['progress_status', 'created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['event_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['subject_type', 'subject_id']);
        });

        Schema::table('spare_parts', function (Blueprint $table) {
            $table->dropIndex(['stock_quantity']);
        });

        Schema::table('clinical_notes', function (Blueprint $table) {
            $table->dropIndex(['is_pinned']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};
