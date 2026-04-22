<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * R-002 — Idempotence des webhooks Billing.
 *
 * Ajoute une colonne `idempotency_key` avec contrainte UNIQUE sur
 * `billing_webhook_logs` pour bloquer en DB le retraitement d'un webhook
 * déjà reçu (retries Stripe, CinetPay, PayPal, etc.).
 *
 * Colonne nullable : MySQL autorise plusieurs NULL sous UNIQUE (NULL ≠ NULL),
 * ce qui garantit la rétrocompatibilité avec les anciens logs pré-R-002.
 *
 * Voir ADR-003-webhook-idempotency-strategy.
 */
return new class extends Migration
{
    protected $connection = 'system';

    public function up(): void
    {
        if (! Schema::connection($this->connection)->hasTable('billing_webhook_logs')) {
            return;
        }

        if (! Schema::connection($this->connection)->hasColumn('billing_webhook_logs', 'idempotency_key')) {
            Schema::connection($this->connection)->table('billing_webhook_logs', function (Blueprint $table) {
                $table->string('idempotency_key', 128)
                    ->nullable()
                    ->after('gateway_slug');

                $table->unique('idempotency_key', 'uq_billing_webhook_logs_idempotency_key');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::connection($this->connection)->hasTable('billing_webhook_logs')) {
            return;
        }

        if (Schema::connection($this->connection)->hasColumn('billing_webhook_logs', 'idempotency_key')) {
            Schema::connection($this->connection)->table('billing_webhook_logs', function (Blueprint $table) {
                $table->dropUnique('uq_billing_webhook_logs_idempotency_key');
                $table->dropColumn('idempotency_key');
            });
        }
    }
};
