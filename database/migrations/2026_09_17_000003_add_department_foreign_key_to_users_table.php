<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a real foreign key so department assignments are referentially intact.
     */
    public function up(): void
    {
        $this->detachOrphanedUsers();

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });
    }

    /**
     * Clear any pre-existing department references that no longer resolve.
     */
    protected function detachOrphanedUsers(): void
    {
        $validIds = DB::table('departments')->select('id')->pluck('id');

        DB::table('users')
            ->whereNotNull('department_id')
            ->whereNotIn('department_id', $validIds)
            ->update(['department_id' => null]);
    }
};
