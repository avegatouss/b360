<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-4 — BonCommande menuiserie (issu d'un devis accepté).
 *
 * Numérotation atomique pattern ADR-006 — UNIQUE(instance_id, numero).
 * `client_id` = ref applicative eshop_customers (cohérence ADR-021).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_bon_commandes')) {
            return;
        }

        Schema::create('mnu_bon_commandes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('numero', 30);                                   // BC-YYYY-NNNN
            $table->unsignedBigInteger('devis_id');                         // FK mnu_devis (P1)
            $table->unsignedBigInteger('client_id');                        // ref applicative eshop_customers
            $table->unsignedBigInteger('chantier_id')->nullable();          // créé par P2-11 transformation
            $table->unsignedBigInteger('facture_acompte_id')->nullable();   // FK mnu_invoices (P2-7)
            $table->string('statut', 30)->default('cree');                  // cree, en_production, livre, cloture
            $table->decimal('montant_ht', 14, 2);
            $table->decimal('taux_tva', 5, 4)->default(0.18);
            $table->decimal('montant_tva', 14, 2);
            $table->decimal('montant_ttc', 14, 2);
            $table->decimal('acompte_pct', 5, 2)->default(30);              // % acompte par défaut 30%
            $table->date('date_livraison_prevue')->nullable();
            $table->date('date_livraison_reelle')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'numero'], 'mnu_bc_instance_numero_unique');
            $table->index(['instance_id', 'devis_id'], 'mnu_bc_instance_devis_idx');
            $table->index(['instance_id', 'client_id'], 'mnu_bc_instance_client_idx');
            $table->index(['instance_id', 'statut'], 'mnu_bc_instance_statut_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_bon_commandes');
    }
};
