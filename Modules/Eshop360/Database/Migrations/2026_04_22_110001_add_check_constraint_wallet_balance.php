<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * R-003 — Solde portefeuille : contrainte SGBD `wallet_balance >= 0`.
 *
 * Ligne de défense ultime contre un solde négatif. Les couches applicatives
 * (FinanceService::debitWallet, WalletDriver) empêchent normalement le
 * débit au-delà du solde, mais un bug applicatif ou un accès direct à la
 * table (ex. seeder, import externe) pourrait contourner ces garde-fous.
 * Le CHECK SGBD refuse systématiquement un UPDATE qui rendrait
 * wallet_balance négatif.
 *
 * SQLite ne supporte pas ALTER TABLE ADD CONSTRAINT : la migration est
 * volontairement no-op sur ce driver (tests unitaires). En production
 * (MySQL / PostgreSQL) la contrainte forme le filet final de R-003.
 *
 * Voir ADR-004-wallet-integrity-strategy.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('eshop_customers')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite : ALTER TABLE ADD CONSTRAINT non supporté.
            // La contrainte applicative FinanceService::debitWallet garantit
            // l'invariant en tests. Voir ADR-004.
            return;
        }

        DB::statement(
            'ALTER TABLE eshop_customers ADD CONSTRAINT chk_wallet_balance_non_negative CHECK (wallet_balance >= 0)'
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('eshop_customers')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE eshop_customers DROP CONSTRAINT chk_wallet_balance_non_negative');
    }
};
