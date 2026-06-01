<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-2 : journal des mouvements de stock matière.
 *
 * Audit trail immuable : entrée fournisseur, sortie consommation OF,
 * réservation, libération, ajustement manuel, inventaire. Idempotence
 * via `reference` UNIQUE (pattern ADR-003 webhook idempotence).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_mouvements_stock')) {
            return;
        }

        Schema::create('mnu_mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('matiere_id');
            $table->string('type', 30);                // entree, sortie, ajustement,
            // reservation, release, inventaire
            $table->decimal('quantite', 14, 4);        // signée ou positive selon type
            $table->decimal('quantite_apres', 14, 4); // snapshot post-mouvement
            $table->string('reference', 128);         // ex. 'OF-2026-0042', 'BC-2026-0001'
            $table->text('motif')->nullable();
            $table->unsignedBigInteger('effectue_par')->nullable();   // user_id
            $table->timestamps();

            // UNIQUE (instance_id, type, reference) garantit idempotence :
            // un même mouvement (même type, même référence) ne peut être enregistré
            // qu'une fois par instance — pattern ADR-003.
            $table->unique(['instance_id', 'type', 'reference'], 'mnu_mvt_instance_type_ref_unique');
            $table->index(['instance_id', 'matiere_id', 'created_at'], 'mnu_mvt_matiere_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_mouvements_stock');
    }
};
