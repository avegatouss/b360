<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-2 : bibliothèque des types de produits menuiserie standards.
 *
 * Catalogue des produits-types (fenêtres, baies coulissantes, portes, etc.)
 * — sert de modèle de devis prérempli avec dimensions standard, matière
 * principale et coût indicatif. Distinct de `mnu_matieres_premieres` qui
 * sont les matières CONSOMMÉES, pas les produits VENDUS.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_types_produits_menuiserie')) {
            return;
        }

        Schema::create('mnu_types_produits_menuiserie', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('code', 50);
            $table->string('nom', 200);
            $table->string('categorie', 50);              // fenetre, baie, porte, garde_corps, autre
            $table->text('description')->nullable();
            $table->unsignedInteger('largeur_standard_mm')->nullable();
            $table->unsignedInteger('hauteur_standard_mm')->nullable();
            $table->decimal('prix_indicatif_ht', 12, 2)->nullable();
            $table->decimal('cout_indicatif', 12, 2)->nullable();
            $table->json('matieres_principales')->nullable();   // [matiere_id, qte_par_unite]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'code'], 'mnu_types_prods_instance_code_unique');
            $table->index(['instance_id', 'categorie'], 'mnu_types_prods_instance_cat_idx');
            $table->index(['instance_id', 'is_active'], 'mnu_types_prods_instance_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_types_produits_menuiserie');
    }
};
