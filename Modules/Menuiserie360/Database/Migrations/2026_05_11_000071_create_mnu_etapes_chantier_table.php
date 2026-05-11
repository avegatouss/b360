<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-11 — Étapes de chantier (jalons + avancement).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_etapes_chantier')) {
            return;
        }

        Schema::create('mnu_etapes_chantier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('chantier_id');
            $table->string('nom', 200);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->unsignedTinyInteger('avancement_pct')->default(0); // 0..100
            $table->string('statut', 30)->default('a_faire');           // a_faire, en_cours, fait, bloque
            $table->timestamp('demarree_at')->nullable();
            $table->timestamp('terminee_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['instance_id', 'chantier_id'], 'mnu_etapes_instance_chantier_idx');
            $table->index('chantier_id', 'mnu_etapes_chantier_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_etapes_chantier');
    }
};
