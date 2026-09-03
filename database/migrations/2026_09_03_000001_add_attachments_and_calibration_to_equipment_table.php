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
            $table->string('photo_path')->nullable()->after('description');
            $table->string('manual_path')->nullable()->after('photo_path');
            $table->date('last_calibrated_at')->nullable()->after('manual_path');
            $table->date('next_calibration_due')->nullable()->after('last_calibrated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'manual_path', 'last_calibrated_at', 'next_calibration_due']);
        });
    }
};
