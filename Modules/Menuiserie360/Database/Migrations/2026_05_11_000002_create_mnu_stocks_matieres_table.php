<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-2 : niveau de stock courant par matière première.
 *
 * Une ligne par (instance, matière). `quantite_actuelle` = stock physique,
 * `quantite_reservee` = total réservé pour OF/devis non encore consommés.
 * `quantite_disponible` = actuelle - reservee (computed côté service).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_stocks_matieres')) {
            return;
        }

        Schema::create('mnu_stocks_matieres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('matiere_id');
            $table->decimal('quantite_actuelle', 14, 4)->default(0);
            $table->decimal('quantite_reservee', 14, 4)->default(0);
            $table->timestamp('derniere_entree_at')->nullable();
            $table->timestamp('derniere_sortie_at')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'matiere_id'], 'mnu_stocks_instance_matiere_unique');
            $table->index(['instance_id'], 'mnu_stocks_instance_idx');

            // Garde-fou applicatif via migration : quantité ne peut être négative.
            // Pattern hérité d'ADR-004 wallet integrity (CHECK SGBD MySQL/PG, no-op SQLite).
            $driver = Schema::getConnection()->getDriverName();
            if (in_array($driver, ['mysql', 'pgsql'], true)) {
                $tableName = 'mnu_stocks_matieres';
                Schema::getConnection()->statement(
                    "ALTER TABLE {$tableName} ADD CONSTRAINT mnu_stocks_qte_actuelle_non_neg CHECK (quantite_actuelle >= 0)"
                );
                Schema::getConnection()->statement(
                    "ALTER TABLE {$tableName} ADD CONSTRAINT mnu_stocks_qte_reservee_non_neg CHECK (quantite_reservee >= 0)"
                );
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_stocks_matieres');
    }
};
