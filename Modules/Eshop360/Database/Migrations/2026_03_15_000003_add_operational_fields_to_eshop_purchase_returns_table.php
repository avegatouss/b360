<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_purchase_returns', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->constrained('eshop_warehouses')->nullOnDelete();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('processed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('eshop_purchase_returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropColumn(['created_by', 'processed_at']);
        });
    }
};
