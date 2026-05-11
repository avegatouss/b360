<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-4 : pivot/extension du Customer Eshop360 avec attributs propres
 * menuiserie (préférences, statistiques agrégées).
 *
 * Contrainte ADR-021 §1 : pas de FK SQL vers `eshop_customers` — la relation
 * est applicative via `customer_id` (validé par CustomerReader::customerExists).
 * Cela évite tout couplage cross-module au niveau schéma.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mnu_clients_menuiserie')) {
            return;
        }

        Schema::create('mnu_clients_menuiserie', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('customer_id'); // référence applicative vers eshop_customers.id
            $table->string('preferred_contact_method', 30)->nullable(); // sms, whatsapp, email, phone
            $table->unsignedInteger('total_chantiers_count')->default(0);
            $table->decimal('total_revenue_xof', 14, 2)->default(0);
            $table->text('notes_menuiserie')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'customer_id'], 'mnu_clients_instance_customer_unique');
            $table->index('instance_id', 'mnu_clients_instance_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mnu_clients_menuiserie');
    }
};
