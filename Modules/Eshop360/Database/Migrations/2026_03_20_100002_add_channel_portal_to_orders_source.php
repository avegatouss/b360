<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE eshop_orders MODIFY COLUMN source ENUM('pos', 'online', 'manual', 'channel_portal') DEFAULT 'pos'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE eshop_orders MODIFY COLUMN source ENUM('pos', 'online', 'manual') DEFAULT 'pos'");
        }
    }
};
