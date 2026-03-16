<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_customers', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->constrained('eshop_customer_groups')->nullOnDelete();
            $table->decimal('wallet_balance', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->foreignId('store_id')->nullable()->constrained('eshop_stores')->nullOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('company_name')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('eshop_customers', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['store_id']);
            $table->dropColumn([
                'group_id',
                'wallet_balance',
                'credit_limit',
                'store_id',
                'date_of_birth',
                'tax_number',
                'company_name',
                'notes',
            ]);
        });
    }
};
