<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_invoices', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('customer_id')
                ->constrained('eshop_projects')->nullOnDelete();
        });

        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('customer_id')
                ->constrained('eshop_projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eshop_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('eshop_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
