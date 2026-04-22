<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * R-002 — Renforce la déduplication des webhooks Eshop360.
 *
 * La migration P0 `2026_04_04_200001_add_p0_safety_guards` avait ajouté
 * la colonne `deduplication_key` et un index non-unique. Le check applicatif
 * `WebhookLog::where('deduplication_key', ...)->exists()` dans
 * `WebhookService::dispatch()` restait donc vulnérable à une race window
 * entre lecture et insertion.
 *
 * Cette migration convertit l'index en contrainte UNIQUE : la garantie
 * est désormais portée par la DB, pas par le code applicatif.
 *
 * Voir ADR-003-webhook-idempotency-strategy.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('eshop_webhook_logs')) {
            return;
        }

        if (! Schema::hasColumn('eshop_webhook_logs', 'deduplication_key')) {
            return;
        }

        // SQLite ne permet pas de DROP INDEX nommé via Schema standard pour
        // un index posé sans nom explicite. Sur MySQL/PostgreSQL, Laravel
        // sait retrouver l'index basé sur le nom canonique
        // 'eshop_webhook_logs_deduplication_key_index'.
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('eshop_webhook_logs', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                $table->dropIndex(['deduplication_key']);
            }
            $table->unique('deduplication_key', 'uq_eshop_webhook_logs_dedup_key');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('eshop_webhook_logs')) {
            return;
        }

        if (! Schema::hasColumn('eshop_webhook_logs', 'deduplication_key')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        Schema::table('eshop_webhook_logs', function (Blueprint $table) use ($driver) {
            $table->dropUnique('uq_eshop_webhook_logs_dedup_key');
            if ($driver !== 'sqlite') {
                $table->index('deduplication_key');
            }
        });
    }
};
