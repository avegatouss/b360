<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-031 / Lot 3 — Liaison polymorphe document financier miroir ↔ facture locale.
 *
 * Aucune colonne ajoutée à `eshop_*` / `mnu_*` : la correspondance vit ici.
 * `linkable_type` = short-key libre (`mnu.invoice`, `eshop.invoice`) — pas un
 * morphTo Eloquent classique. L'unicité (instance, type, id) est la clé
 * d'idempotence du push / backfill (1 facture locale = 1 document miroir).
 * Additive — `down()` = drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ref_finance_links')) {
            return;
        }

        Schema::create('ref_finance_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('document_id');             // FK applicatif → ref_documents_finance
            $table->string('linkable_type', 50);                   // mnu.invoice | eshop.invoice
            $table->unsignedBigInteger('linkable_id');             // id local dans le module
            $table->timestamps();

            // Une facture locale = au plus 1 document miroir (idempotence push/backfill).
            $table->unique(
                ['instance_id', 'linkable_type', 'linkable_id'],
                'ref_finance_links_instance_linkable_unique'
            );
            $table->index(['instance_id', 'document_id'], 'ref_finance_links_instance_document_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_finance_links');
    }
};
