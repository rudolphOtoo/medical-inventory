<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the manufacturer and description columns the spare parts catalogue
     * has always presented in the UI but were never present in the schema.
     */
    public function up(): void
    {
        Schema::table('spare_parts', function (Blueprint $table): void {
            $table->string('manufacturer')->nullable()->after('part_number');
            $table->text('description')->nullable()->after('manufacturer');
        });
    }

    public function down(): void
    {
        Schema::table('spare_parts', function (Blueprint $table): void {
            $table->dropColumn(['manufacturer', 'description']);
        });
    }
};
