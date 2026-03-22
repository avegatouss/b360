<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Extend enum to include new roles
        DB::statement("ALTER TABLE eshop_channel_users MODIFY COLUMN role ENUM('admin','manager','operator','cashier','viewer','client') DEFAULT 'viewer'");

        // Map old values
        DB::table('eshop_channel_users')->where('role', 'manager')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        DB::table('eshop_channel_users')->where('role', 'admin')->update(['role' => 'manager']);
        DB::table('eshop_channel_users')->where('role', 'cashier')->update(['role' => 'operator']);
        DB::table('eshop_channel_users')->where('role', 'client')->update(['role' => 'viewer']);
        DB::statement("ALTER TABLE eshop_channel_users MODIFY COLUMN role ENUM('manager','operator','viewer') DEFAULT 'viewer'");
    }
};
