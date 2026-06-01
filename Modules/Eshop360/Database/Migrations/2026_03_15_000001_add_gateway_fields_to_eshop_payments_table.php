<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_payments', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('method');
            $table->string('gateway_reference')->nullable()->after('reference');
            $table->json('metadata')->nullable()->after('notes');
            $table->index(['gateway', 'gateway_reference'], 'eshop_payments_gateway_ref_idx');
        });
    }

    public function down(): void
    {
        Schema::table('eshop_payments', function (Blueprint $table) {
            $table->dropIndex('eshop_payments_gateway_ref_idx');
            $table->dropColumn(['gateway', 'gateway_reference', 'metadata']);
        });
    }
};
