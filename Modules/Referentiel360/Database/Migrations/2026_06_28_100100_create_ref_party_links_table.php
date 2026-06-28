<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-030 / Lot 1 — Liaison polymorphe golden record ↔ objet local module.
 *
 * Aucune colonne ajoutée à `eshop_*` / `mnu_*` : la correspondance vit ici.
 * `linkable_type` = short-key libre (`mnu.client`, `eshop.customer`, …) — pas
 * un morphTo Eloquent classique. Additive — `down()` = drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ref_party_links')) {
            return;
        }

        Schema::create('ref_party_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('party_id');                 // FK applicatif → ref_parties
            $table->string('linkable_type', 50);                    // mnu.client | mnu.supplier | eshop.customer | eshop.supplier
            $table->unsignedBigInteger('linkable_id');              // id local dans le module
            $table->timestamps();

            // Un objet local = au plus 1 party (clé d'idempotence du backfill).
            $table->unique(
                ['instance_id', 'linkable_type', 'linkable_id'],
                'ref_party_links_instance_linkable_unique'
            );
            $table->index(['instance_id', 'party_id'], 'ref_party_links_instance_party_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_party_links');
    }
};
