<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-8 — Découpes aluminium effectuées sur un OF (traçabilité atelier).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_decoupes')) {
            return;
        }

        Schema::create('mnu_decoupes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('of_ligne_id');
            $table->unsignedBigInteger('matiere_id');                  // ref mnu_matieres_premieres
            $table->decimal('longueur_mm', 12, 2);                     // longueur de la découpe
            $table->unsignedSmallInteger('quantite')->default(1);
            $table->unsignedBigInteger('effectue_par')->nullable();    // user_id atelier
            $table->timestamp('decoupe_at')->nullable();
            $table->timestamps();

            $table->index(['instance_id', 'of_ligne_id'], 'mnu_decoupes_instance_of_ligne_idx');
            $table->index(['instance_id', 'matiere_id'], 'mnu_decoupes_instance_matiere_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_decoupes');
    }
};
