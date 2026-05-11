<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-1 : catalogue des matières premières menuiserie aluminium.
 *
 * Profilés aluminium (en mètres linéaires), vitrages (en m²), accessoires
 * (en pièces). Unité de mesure variable par matière — différent du modèle
 * stock unitaire d'Eshop360 (qui compte en pièces). Pas d'overlap avec
 * `eshop_products` (sémantique différente).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_matieres_premieres')) {
            return;
        }

        Schema::create('mnu_matieres_premieres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('code', 50);
            $table->string('designation', 200);
            $table->string('categorie', 50);          // profile_alu, vitrage, accessoire, autre
            $table->string('unite', 20);              // m_lineaire, m2, piece
            $table->decimal('prix_unitaire', 12, 4)->default(0);
            $table->decimal('seuil_alerte', 12, 4)->default(0);
            $table->string('fournisseur_principal', 200)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'code'], 'mnu_matieres_instance_code_unique');
            $table->index(['instance_id', 'categorie'], 'mnu_matieres_instance_cat_idx');
            $table->index(['instance_id', 'is_active'], 'mnu_matieres_instance_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_matieres_premieres');
    }
};
