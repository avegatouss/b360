<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-030 / Lot 1 — Golden record « tiers » (clients + fournisseurs).
 *
 * Référentiel mince : identité partagée seulement. Les attributs métier
 * propres (loyalty/wallet Eshop, statut/total_chantiers Menuiserie) restent
 * dans les tables des modules. Additive — `down()` = drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ref_parties')) {
            return;
        }

        Schema::create('ref_parties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->char('party_uid', 26);                          // ULID stable cross-module

            $table->boolean('is_customer')->default(false);
            $table->boolean('is_supplier')->default(false);
            $table->string('person_type', 20)->default('particulier'); // particulier | entreprise

            $table->string('display_name', 200);
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('legal_name', 200)->nullable();

            $table->string('email', 150)->nullable();               // normalisé lower/trim
            $table->string('phone', 30)->nullable();                // normalisé
            $table->string('phone_secondary', 30)->nullable();

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->char('country', 2)->default('CI');

            $table->string('tax_id_rccm', 50)->nullable();
            $table->string('tax_id_nif', 50)->nullable();

            $table->string('supplier_category', 50)->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->smallInteger('lead_time_days')->nullable();
            $table->string('currency', 3)->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->string('source_module', 30)->nullable();        // menuiserie | eshop | manual

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'party_uid'], 'ref_parties_instance_uid_unique');
            $table->index(['instance_id', 'is_customer'], 'ref_parties_instance_customer_idx');
            $table->index(['instance_id', 'is_supplier'], 'ref_parties_instance_supplier_idx');
            $table->index(['instance_id', 'email'], 'ref_parties_instance_email_idx');
            $table->index(['instance_id', 'phone'], 'ref_parties_instance_phone_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_parties');
    }
};
