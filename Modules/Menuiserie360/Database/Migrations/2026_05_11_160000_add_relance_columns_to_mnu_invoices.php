<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P3-7 — Suivi des relances automatiques sur facture impayée.
 *
 * Migration additive (CLAUDE.md §6) : 2 colonnes nullable, pas de
 * dropColumn ailleurs. `relance_count` = compteur cumulé des
 * notifications envoyées, `last_relance_at` = horodatage dernier envoi
 * pour anti-flood (>= 7j entre deux relances par défaut).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mnu_invoices')) {
            return;
        }

        Schema::table('mnu_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('mnu_invoices', 'relance_count')) {
                $table->unsignedInteger('relance_count')->default(0)->after('paid_amount');
            }
            if (! Schema::hasColumn('mnu_invoices', 'last_relance_at')) {
                $table->timestamp('last_relance_at')->nullable()->after('relance_count');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mnu_invoices')) {
            return;
        }

        Schema::table('mnu_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('mnu_invoices', 'last_relance_at')) {
                $table->dropColumn('last_relance_at');
            }
            if (Schema::hasColumn('mnu_invoices', 'relance_count')) {
                $table->dropColumn('relance_count');
            }
        });
    }
};
