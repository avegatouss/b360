<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-8 — Ordre de fabrication menuiserie.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_ordres_fabrication')) {
            return;
        }

        Schema::create('mnu_ordres_fabrication', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('numero', 30);                              // OF-YYYY-NNNN
            $table->unsignedBigInteger('bc_id');                       // FK mnu_bon_commandes
            $table->string('statut', 30)->default('en_attente');       // en_attente, en_cours, termine, controle, livre
            $table->date('date_planifiee')->nullable();
            $table->date('date_demarrage')->nullable();
            $table->date('date_fin_reelle')->nullable();
            $table->unsignedBigInteger('chef_atelier_id')->nullable(); // user_id
            $table->text('notes_atelier')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'numero'], 'mnu_of_instance_numero_unique');
            $table->index(['instance_id', 'bc_id'], 'mnu_of_instance_bc_idx');
            $table->index(['instance_id', 'statut'], 'mnu_of_instance_statut_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_ordres_fabrication');
    }
};
