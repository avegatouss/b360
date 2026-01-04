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
        Schema::create('personne_physiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personne_id')->constrained()->onDelete('cascade');
            $table->string('civilite', 10)->nullable();
            $table->string('nom');
            $table->string('prenom');
           $table->string('nom_affichage')->nullable();
            $table->string('nom_complet')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('photo')->nullable();
            $table->string('profession')->nullable();
            $table->string('type_piece')->nullable();
            $table->string('numero_piece')->nullable();
            $table->string('lieu_delivrance')->nullable();
            $table->date('date_delivrance')->nullable();
            $table->date('date_expiration')->nullable();
             $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personne_physiques');
    }
};
