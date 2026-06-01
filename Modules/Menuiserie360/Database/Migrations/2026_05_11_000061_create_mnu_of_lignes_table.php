<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-8 — Lignes d'OF (un produit-ligne par item à fabriquer).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_of_lignes')) {
            return;
        }

        Schema::create('mnu_of_lignes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('of_id');
            $table->unsignedBigInteger('bc_item_id')->nullable();      // ref mnu_bc_items
            $table->string('designation', 200);
            $table->unsignedSmallInteger('quantite')->default(1);
            $table->unsignedInteger('largeur_mm')->nullable();
            $table->unsignedInteger('hauteur_mm')->nullable();
            $table->string('statut_ligne', 30)->default('a_fabriquer');  // a_fabriquer, en_cours, fait, controle_ok
            $table->timestamps();

            $table->index(['instance_id', 'of_id'], 'mnu_of_lignes_instance_of_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_of_lignes');
    }
};
