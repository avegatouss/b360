<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eshop_fne_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->index();
            $table->morphs('invoiceable'); // order, invoice, quotation...
            $table->string('fne_reference')->nullable()->index();       // ex: 9606123E25000000019
            $table->string('fne_id')->nullable();                       // UUID from DGI
            $table->string('fne_token')->nullable();                    // verification URL
            $table->string('fne_ncc')->nullable();                      // NCC contribuable
            $table->string('template')->default('B2C');                 // B2B, B2C, B2G, B2F
            $table->string('status')->default('pending');               // pending, signed, failed, refunded
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('error_message')->nullable();
            $table->unsignedBigInteger('signed_by')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'fne_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eshop_fne_invoices');
    }
};
