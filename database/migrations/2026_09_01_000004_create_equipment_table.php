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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->nullable()->unique();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('location')->nullable();
            $table->string('status')->default('in_use'); // InUse, UnderReview, OutForRepair, OutOfService, Retired, Lost
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_archived')->default(false)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
