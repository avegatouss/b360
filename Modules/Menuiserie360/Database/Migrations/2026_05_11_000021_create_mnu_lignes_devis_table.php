<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-6 : lignes de devis (détail dimensionné).
 *
 * Chaque ligne porte ses dimensions (largeur/hauteur en mm), une matière
 * principale optionnelle (FK applicative vers mnu_matieres_premieres) et
 * son prix unitaire HT. Le calcul du montant ligne se fait dans
 * DevisCalculatorService (P1-7).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_lignes_devis')) {
            return;
        }

        Schema::create('mnu_lignes_devis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('devis_id');
            $table->unsignedBigInteger('matiere_id')->nullable();        // ref applicative mnu_matieres
            $table->string('designation', 200);
            $table->unsignedSmallInteger('quantite')->default(1);
            $table->unsignedInteger('largeur_mm')->nullable();           // pour calcul m²/m linéaire
            $table->unsignedInteger('hauteur_mm')->nullable();
            $table->decimal('prix_unitaire_ht', 12, 4);
            $table->decimal('remise_ligne', 12, 2)->default(0);
            $table->decimal('montant_ht', 14, 2)->default(0);            // calculé par service
            $table->decimal('cout_revient', 14, 2)->default(0);          // pour calcul marge
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();

            $table->index(['instance_id', 'devis_id'], 'mnu_lignes_devis_instance_devis_idx');
            $table->index('devis_id', 'mnu_lignes_devis_devis_idx');
            $table->index(['instance_id', 'matiere_id'], 'mnu_lignes_devis_instance_matiere_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_lignes_devis');
    }
};
