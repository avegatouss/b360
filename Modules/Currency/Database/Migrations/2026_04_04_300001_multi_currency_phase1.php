<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-currency Phase 1 — Extend Currency module for multi-tenant support.
 *
 * Adds: instance_id on currencies, tenant_currency_settings, exchange_rate_history.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Add instance_id to currencies (tenant override)
        if (Schema::hasTable('currencies') && !Schema::hasColumn('currencies', 'instance_id')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->unsignedBigInteger('instance_id')->nullable()->after('id');
                $table->index(['instance_id', 'is_active']);
            });
        }

        // 2. Tenant-level currency configuration
        if (!Schema::hasTable('tenant_currency_settings')) {
            Schema::create('tenant_currency_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('instance_id')->unique();
                $table->string('default_currency', 10)->default('XOF');
                $table->json('allowed_currencies')->nullable();
                $table->boolean('multi_currency_enabled')->default(false);
                $table->boolean('auto_update_rates')->default(true);
                $table->string('primary_api_source', 100)->default('open.er-api.com');
                $table->string('fallback_api_source', 100)->nullable();
                $table->timestamps();
            });
        }

        // 3. Historical exchange rates (immutable audit trail)
        if (!Schema::hasTable('exchange_rate_history')) {
            Schema::create('exchange_rate_history', function (Blueprint $table) {
                $table->id();
                $table->string('base_code', 10);
                $table->string('target_code', 10);
                $table->decimal('rate', 20, 10);
                $table->string('source', 100); // 'open.er-api.com' | 'exchangerate-api.com' | 'manual'
                $table->timestamp('fetched_at');
                $table->index(['base_code', 'target_code', 'fetched_at'], 'idx_rate_history_lookup');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_history');
        Schema::dropIfExists('tenant_currency_settings');

        if (Schema::hasTable('currencies') && Schema::hasColumn('currencies', 'instance_id')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->dropIndex(['instance_id', 'is_active']);
                $table->dropColumn('instance_id');
            });
        }
    }
};
