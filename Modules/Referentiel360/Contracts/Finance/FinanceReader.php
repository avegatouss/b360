<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Finance;

/**
 * Contrat de LECTURE du registre financier miroir (ADR-031 / Lot 3).
 *
 * Implémentation par défaut :
 * {@see \Modules\Referentiel360\Adapters\Eloquent\EloquentFinanceReader}.
 *
 * Toutes les méthodes sont scopées par instance.
 */
interface FinanceReader
{
    public function find(int $instanceId, int $documentId): ?FinanceDto;

    /**
     * Résout un document miroir à partir d'un lien facture locale (short-key + id local).
     */
    public function getByLink(int $instanceId, string $linkType, int $localId): ?FinanceDto;

    /**
     * Agrégation PAR DEVISE pour le reporting consolidé d'une instance.
     *
     * Clé = code devise (`XOF`, `EUR`, …). On ne somme jamais des devises
     * hétérogènes. Les avoirs (`credit_note`) sont SOUSTRAITS ; les documents
     * annulés exclus.
     *
     * @return array<string, array{ttc: string, paid: string, due: string}>
     */
    public function totalsForInstance(int $instanceId): array;
}
