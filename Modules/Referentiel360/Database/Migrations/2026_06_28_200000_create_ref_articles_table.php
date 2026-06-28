<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-030 / Lot 2 — Golden record « article » (catalogue mince).
 *
 * Référentiel mince : identité catalogue partagée seulement. Le BOM
 * (mnu_catalog_item_components), les variations (eshop_product_variations),
 * le stock et la péremption restent dans les tables des modules L3.
 * Additive — `down()` = drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ref_articles')) {
            return;
        }

        Schema::create('ref_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->char('article_uid', 26);                        // ULID stable cross-module

            $table->string('code', 60);                             // code / sku / code source
            $table->string('label', 200);                           // libelle / designation / name
            $table->string('article_type', 20)->default('produit'); // produit | matiere | service | autre

            $table->string('unit', 20)->nullable();                 // u, ml, m², kg, h, pc…
            $table->decimal('sale_price', 12, 4)->nullable();       // prix de vente HT (null pour matière)
            $table->decimal('tax_rate', 5, 4)->nullable();          // taux TVA
            $table->string('category_label', 100)->nullable();      // catégorie source (libre)
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->string('source_module', 30)->nullable();        // menuiserie | eshop | manual

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['instance_id', 'article_uid'], 'ref_articles_instance_uid_unique');
            $table->index(['instance_id', 'code'], 'ref_articles_instance_code_idx');
            $table->index(['instance_id', 'article_type'], 'ref_articles_instance_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_articles');
    }
};
