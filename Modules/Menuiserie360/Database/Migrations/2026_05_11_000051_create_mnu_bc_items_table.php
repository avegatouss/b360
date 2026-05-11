<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-4 — Items d'un BonCommande (snapshot des lignes devis acceptées).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_bc_items')) {
            return;
        }

        Schema::create('mnu_bc_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('bc_id');
            $table->unsignedBigInteger('ligne_devis_source_id')->nullable();  // ref mnu_lignes_devis
            $table->unsignedBigInteger('matiere_id')->nullable();
            $table->string('designation', 200);
            $table->unsignedSmallInteger('quantite')->default(1);
            $table->unsignedInteger('largeur_mm')->nullable();
            $table->unsignedInteger('hauteur_mm')->nullable();
            $table->decimal('prix_unitaire_ht', 12, 4);
            $table->decimal('montant_ht', 14, 2);
            $table->decimal('cout_revient', 14, 2)->default(0);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();

            $table->index(['instance_id', 'bc_id'], 'mnu_bc_items_instance_bc_idx');
            $table->index('bc_id', 'mnu_bc_items_bc_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_bc_items');
    }
};
