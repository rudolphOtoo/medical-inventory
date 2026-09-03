<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE spare_parts ADD CONSTRAINT spare_parts_stock_positive CHECK (stock_quantity >= 0)',
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            'ALTER TABLE spare_parts DROP CONSTRAINT spare_parts_stock_positive',
        );
    }
};
