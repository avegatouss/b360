<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-031 / Lot 3 — Registre financier MIROIR (golden mince, lecture seule).
 *
 * Reflète les factures émises par les modules L3 (Menuiserie360 / Eshop360) pour
 * un CA / encaissement / reste-dû consolidé par instance et par tiers. Le
 * référentiel ne crée, ne numérote, ni ne modifie JAMAIS une facture légale :
 * `document_number` est toujours COPIÉ de la source. `status_normalized` et
 * `due_amount` sont DÉRIVÉS des montants (cf EloquentFinanceWriter).
 *
 * Additive — `down()` = drop. Idempotence `hasTable`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ref_documents_finance')) {
            return;
        }

        Schema::create('ref_documents_finance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->char('document_uid', 26);                       // ULID stable cross-module

            $table->unsignedBigInteger('party_id')->nullable();     // FK applicatif → ref_parties (résolu via PartyReader)
            $table->string('doc_type', 20)->default('invoice');     // invoice | credit_note
            $table->string('document_number', 50);                  // numéro légal COPIÉ du module (jamais régénéré)
            $table->string('currency', 3)->default('XOF');

            $table->decimal('amount_ht', 14, 2)->default(0);
            $table->decimal('amount_tax', 14, 2)->default(0);
            $table->decimal('amount_ttc', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('due_amount', 14, 2)->default(0);       // dérivé ttc - paid

            $table->string('status_normalized', 20)->default('issued'); // dérivé (issued|partially_paid|paid|cancelled)
            $table->boolean('is_cancelled')->default(false);

            $table->timestamp('issued_at')->nullable();
            $table->date('due_date')->nullable();

            $table->string('source_module', 30);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'document_uid'], 'ref_docfin_instance_uid_unique');
            $table->index(['instance_id', 'party_id'], 'ref_docfin_instance_party_idx');
            $table->index(['instance_id', 'status_normalized'], 'ref_docfin_instance_status_idx');
            $table->index(['instance_id', 'source_module'], 'ref_docfin_instance_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_documents_finance');
    }
};
