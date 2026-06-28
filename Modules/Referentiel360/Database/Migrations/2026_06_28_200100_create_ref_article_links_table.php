<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-030 / Lot 2 — Liaison polymorphe golden record article ↔ objet local module.
 *
 * Aucune colonne ajoutée à `eshop_*` / `mnu_*` : la correspondance vit ici.
 * `linkable_type` = short-key libre (`mnu.catalog_item`, `mnu.matiere`,
 * `eshop.product`) — pas un morphTo Eloquent classique. Additive — `down()` = drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ref_article_links')) {
            return;
        }

        Schema::create('ref_article_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('article_id');               // FK applicatif → ref_articles
            $table->string('linkable_type', 50);                    // mnu.catalog_item | mnu.matiere | eshop.product
            $table->unsignedBigInteger('linkable_id');              // id local dans le module
            $table->timestamps();

            // Un objet local = au plus 1 article (clé d'idempotence du backfill).
            $table->unique(
                ['instance_id', 'linkable_type', 'linkable_id'],
                'ref_article_links_instance_linkable_unique'
            );
            $table->index(['instance_id', 'article_id'], 'ref_article_links_instance_article_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_article_links');
    }
};
