<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleSource;

/**
 * Lot 2.a (ADR-030) — ArticleSource backfill des matières premières Menuiserie360.
 *
 * Taggée `referentiel.article_source` (cf. ServiceProvider, uniquement si
 * Referentiel360 est activé). Itère les matières premières de l'instance et
 * yield des DTO neutres pour la réconciliation idempotente
 * (`referentiel:backfill-articles`).
 */
final class MenuiserieMatiereArticleSource implements ArticleSource
{
    public function __construct(
        private readonly MenuiserieArticleMapper $mapper,
    ) {}

    public function linkType(): string
    {
        return 'mnu.matiere';
    }

    /**
     * @return iterable<int, ArticleAttributesDto>
     */
    public function each(int $instanceId): iterable
    {
        $matieres = MatierePremiere::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->orderBy('id')
            ->cursor();

        foreach ($matieres as $matiere) {
            yield $this->mapper->fromMatiere($matiere);
        }
    }
}
