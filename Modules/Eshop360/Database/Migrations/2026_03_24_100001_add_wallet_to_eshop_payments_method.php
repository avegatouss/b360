<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE eshop_payments MODIFY COLUMN method ENUM('cash', 'card', 'cheque', 'paypal', 'bank_transfer', 'wallet', 'points', 'deposit', 'gift_card', 'external', 'manual') NOT NULL DEFAULT 'cash'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE eshop_payments MODIFY COLUMN method ENUM('cash', 'card', 'cheque', 'paypal', 'bank_transfer', 'points', 'deposit', 'gift_card', 'external', 'manual') NOT NULL DEFAULT 'cash'");
        }
    }
};
