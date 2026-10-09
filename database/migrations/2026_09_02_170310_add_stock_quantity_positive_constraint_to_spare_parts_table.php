<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Whether the active connection can alter table-level constraints.
     */
    private function supportsConstraintAlteration(): bool
    {
        return DB::connection()->getDriverName() !== 'sqlite';
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->supportsConstraintAlteration()) {
            return;
        }

        DB::statement(
            'ALTER TABLE spare_parts ADD CONSTRAINT spare_parts_stock_positive CHECK (stock_quantity >= 0)',
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->supportsConstraintAlteration()) {
            return;
        }

        DB::statement(
            'ALTER TABLE spare_parts DROP CONSTRAINT spare_parts_stock_positive',
        );
    }
};
