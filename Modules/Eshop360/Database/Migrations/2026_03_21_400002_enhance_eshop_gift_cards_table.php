<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_gift_cards', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('code')
                ->constrained('eshop_customers')->nullOnDelete();
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('batch_id')->nullable()->after('customer_name')->index();
            $table->text('notes')->nullable()->after('expiry_date');
            // status column already has 'disabled' added at runtime but not in enum
            // We'll handle status in code
        });
    }

    public function down(): void
    {
        Schema::table('eshop_gift_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn(['customer_name', 'batch_id', 'notes']);
        });
    }
};
