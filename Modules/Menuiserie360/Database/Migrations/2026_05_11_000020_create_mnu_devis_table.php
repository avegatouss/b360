<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-6 : devis menuiserie aluminium (chapeau + lignes — voir migration 000021).
 *
 * Numérotation atomique pattern ADR-006 — UNIQUE(instance_id, numero).
 * `client_id` = référence applicative vers eshop_customers.id (pas de FK SQL,
 * cohérence ADR-021 §1).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_devis')) {
            return;
        }

        Schema::create('mnu_devis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('numero', 30);                                 // DEV-YYYY-NNNN
            $table->unsignedBigInteger('client_id');                      // ref applicative eshop_customers
            $table->string('statut', 30)->default('brouillon');
            $table->decimal('montant_ht', 14, 2)->default(0);
            $table->decimal('taux_tva', 5, 4)->default(0.18);            // 18% par défaut CI
            $table->decimal('montant_tva', 14, 2)->default(0);
            $table->decimal('montant_ttc', 14, 2)->default(0);
            $table->decimal('remise_globale', 14, 2)->default(0);
            $table->decimal('marge_minimum', 5, 4)->default(0.15);       // 15% par défaut, configurable
            $table->unsignedSmallInteger('validite_jours')->default(30);
            $table->date('date_validite')->nullable();
            $table->text('conditions')->nullable();
            $table->text('notes_internes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();        // user_id
            $table->unsignedBigInteger('valide_par')->nullable();        // user_id direction
            $table->timestamp('valide_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'numero'], 'mnu_devis_instance_numero_unique');
            $table->index(['instance_id', 'statut'], 'mnu_devis_instance_statut_idx');
            $table->index(['instance_id', 'client_id'], 'mnu_devis_instance_client_idx');
            $table->index(['instance_id', 'date_validite'], 'mnu_devis_instance_validite_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_devis');
    }
};
