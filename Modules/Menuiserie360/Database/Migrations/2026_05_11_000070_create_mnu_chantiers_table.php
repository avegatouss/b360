<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-11 — Chantier de pose (suivi terrain).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_chantiers')) {
            return;
        }

        Schema::create('mnu_chantiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('numero', 30);                          // CH-YYYY-NNNN
            $table->unsignedBigInteger('bc_id');                   // FK mnu_bon_commandes
            $table->unsignedBigInteger('client_id');               // ref applicative eshop_customers
            $table->string('statut', 30)->default('en_attente');   // en_attente, en_cours, suspendu, termine, livre
            $table->string('adresse_pose', 500)->nullable();
            $table->string('contact_chantier', 200)->nullable();
            $table->date('date_debut_prevue')->nullable();
            $table->date('date_debut_reelle')->nullable();
            $table->date('date_fin_prevue')->nullable();
            $table->date('date_fin_reelle')->nullable();
            $table->unsignedBigInteger('chef_chantier_id')->nullable();
            $table->json('equipe_user_ids')->nullable();           // [user_id, ...]
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'numero'], 'mnu_chantiers_instance_numero_unique');
            $table->index(['instance_id', 'bc_id'], 'mnu_chantiers_instance_bc_idx');
            $table->index(['instance_id', 'statut'], 'mnu_chantiers_instance_statut_idx');
            $table->index(['instance_id', 'date_fin_prevue'], 'mnu_chantiers_instance_fin_prevue_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_chantiers');
    }
};
