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
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('color')->default('canary'); // canary, mint, azure, coral, lavender
            $table->json('tags')->nullable(); // e.g. ["urgent", "shift-handoff", "calibration-pending"]
            $table->boolean('is_pinned')->default(false);
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('equipment_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
