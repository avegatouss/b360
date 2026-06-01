<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('eshop_orders', 'employee_id')) {
            Schema::table('eshop_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('instance_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('eshop_orders', function (Blueprint $table) {
            if (Schema::hasColumn('eshop_orders', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
        });
    }
};
