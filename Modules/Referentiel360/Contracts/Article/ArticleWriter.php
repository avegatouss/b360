<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Article;

/**
 * Contrat d'ÉCRITURE du golden record article (ADR-030 / Lot 2).
 *
 * Les modules L3 POUSSENT leurs articles via `upsertFromModule`. La résolution
 * (lien existant → sinon nouveau golden, JAMAIS de dédup par code) est déléguée
 * au {@see ArticleResolver}.
 *
 * Implémentation par défaut :
 * {@see \Modules\Referentiel360\Adapters\Eloquent\EloquentArticleWriter}.
 */
interface ArticleWriter
{
    /**
     * Crée ou met à jour le golden record correspondant à l'objet local, et
     * garantit le lien (idempotent sur la clé unique du lien).
     */
    public function upsertFromModule(int $instanceId, string $linkType, ArticleAttributesDto $attrs): ArticleDto;

    /**
     * Crée (ou conserve) le lien entre un article et un objet local.
     */
    public function link(int $instanceId, int $articleId, string $linkType, int $localId): void;
}
