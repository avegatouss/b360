<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-7 / spec v1.3 §4.5 — `mnu_payments` (BC-Finance autonome).
 *
 * Table morphique propre Menuiserie360 (Cas A §1.4ter).
 * `payable_type` = short key 'mnu.invoice' typiquement (cf. morph map
 * propre dans Menuiserie360ServiceProvider::boot()).
 *
 * Idempotence webhook pattern ADR-003 — UNIQUE(instance_id, idempotency_key)
 * permet de re-traiter en silence un webhook Mobile Money rejoué.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_payments')) {
            return;
        }

        Schema::create('mnu_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('payable_type', 50);                  // 'mnu.invoice' (morph short key)
            $table->unsignedBigInteger('payable_id');
            $table->decimal('amount', 14, 2);
            $table->string('method', 30);                        // especes, virement, mobile_money, cheque
            $table->string('gateway', 50)->nullable();           // cinetpay, mtn_momo, orange_money, wave
            $table->string('transaction_ref', 100)->nullable();  // référence externe gateway
            $table->string('idempotency_key', 128)->nullable();  // pattern ADR-003
            $table->string('status', 30)->default('pending');    // pending, succeeded, failed, refunded
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_payload')->nullable();         // payload brut pour audit
            $table->timestamps();

            $table->unique(['instance_id', 'idempotency_key'], 'mnu_payments_instance_idempkey_unique');
            $table->index(['instance_id', 'payable_type', 'payable_id'], 'mnu_payments_morph_idx');
            $table->index(['instance_id', 'status'], 'mnu_payments_instance_status_idx');
            $table->index(['instance_id', 'method'], 'mnu_payments_instance_method_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_payments');
    }
};
