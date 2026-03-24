<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE eshop_channel_users MODIFY COLUMN role ENUM('member','admin','manager','operator','cashier','viewer','client') NOT NULL DEFAULT 'viewer'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE eshop_channel_users MODIFY COLUMN role ENUM('admin','manager','operator','cashier','viewer','client') NOT NULL DEFAULT 'viewer'");
        }
    }
};
