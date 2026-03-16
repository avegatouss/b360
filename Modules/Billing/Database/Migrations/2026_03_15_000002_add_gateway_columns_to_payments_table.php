<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        Schema::connection($this->connection)->table('payments', function (Blueprint $table) {
            $table->string('gateway_slug', 50)->nullable()->after('gateway_id');
            $table->string('gateway_reference', 255)->nullable()->after('gateway_slug');
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('payments', function (Blueprint $table) {
            $table->dropColumn(['gateway_slug', 'gateway_reference', 'refunded_at', 'refund_amount']);
        });
    }
};
