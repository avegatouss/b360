<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER TABLE ADD CONSTRAINT.
            // CHECK constraints must be defined at CREATE TABLE time in SQLite.
            // The lockForUpdate() in StockService already prevents negative stock.
            return;
        }

        DB::statement('ALTER TABLE eshop_stocks ADD CONSTRAINT chk_quantity_non_negative CHECK (quantity >= 0)');
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE eshop_stocks DROP CONSTRAINT chk_quantity_non_negative');
    }
};
