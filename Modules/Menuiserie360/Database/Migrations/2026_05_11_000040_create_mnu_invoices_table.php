<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P2-7 / spec v1.3 §4.5 — `mnu_invoices` (BC-Finance autonome).
 *
 * Schéma natif Menuiserie360 — pas de FK vers `eshop_invoices`.
 * `client_id` = référence applicative vers eshop_customers.id, validée
 * par CustomerReader::customerExists() avant insertion.
 *
 * Numérotation atomique pattern ADR-006 — UNIQUE(instance_id, invoice_number).
 * Concurrence gérée par InvoiceNumberGenerator (P2-7 part 2).
 *
 * `due_amount` est une colonne DERIVED côté SGBD (MySQL 8+/PG) ou recalculée
 * applicativement (SQLite). On stocke `paid_amount` et on dérive `due_amount`
 * pour éviter la dérive entre les deux.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_invoices')) {
            return;
        }

        Schema::create('mnu_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->string('invoice_number', 30);                        // MNU-FAC-YYYY-NNNN
            $table->unsignedBigInteger('client_id');                     // FK applicative eshop_customers
            $table->unsignedBigInteger('bc_id')->nullable();             // FK mnu_bon_commandes (P2-4)
            $table->unsignedBigInteger('chantier_id')->nullable();       // FK mnu_chantiers (P2-11)
            $table->string('type', 20);                                  // acompte, solde, avoir
            $table->decimal('amount_ht', 14, 2);
            $table->decimal('tax_rate', 5, 4)->default(0.18);            // 18% par défaut CI
            $table->decimal('amount_tva', 14, 2);
            $table->decimal('amount_ttc', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('status', 30)->default('draft');              // draft, issued, paid_partial, paid_full, cancelled
            $table->timestamp('issued_at')->nullable();
            $table->string('pdf_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'invoice_number'], 'mnu_invoices_instance_number_unique');
            $table->index(['instance_id', 'status'], 'mnu_invoices_instance_status_idx');
            $table->index(['instance_id', 'client_id'], 'mnu_invoices_instance_client_idx');
            $table->index(['instance_id', 'bc_id'], 'mnu_invoices_instance_bc_idx');
            $table->index(['instance_id', 'chantier_id'], 'mnu_invoices_instance_chantier_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_invoices');
    }
};
