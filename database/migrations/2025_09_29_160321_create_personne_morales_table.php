<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personne_morales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personne_id')->constrained()->onDelete('cascade');
            $table->string('raison_sociale');
            $table->string('sigle')->nullable();
            $table->string('forme_societe')->nullable();
            $table->string('secteur_activite')->nullable();
            $table->string('nom_groupe')->nullable();
            $table->string('logo')->nullable();
            $table->string('rccm')->nullable();
            $table->string('ncc')->nullable();
            $table->string('num_identification_fiscale')->nullable();
            $table->string('siege_social')->nullable();
            $table->string('adresse_siege')->nullable();
             $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personne_morales');
    }
};
