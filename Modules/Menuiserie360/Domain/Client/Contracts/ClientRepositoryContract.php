<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Contracts;

use Modules\Eshop360\Contracts\Customer\CustomerDto;

/**
 * Contrat interne BC-Clients Menuiserie360.
 *
 * Spec v1.3 §1.2 — frontière BC-Clients : "BC-Clients est un agrégat partagé
 * (anti-corruption layer sur le `Customer` Eshop360)".
 *
 * Définition v1.0 — façade qui combine :
 *   1. la lecture identité Eshop360 via `CustomerReader` (ADR-021)
 *   2. les attributs propres Menuiserie360 (ClientMenuiserie : préférences
 *      menuiserie, historique chantiers, etc.) qui vivront dans des tables
 *      `mnu_*` dédiées en P1-4/P1-5.
 *
 * Implémentation à coder en P1-5 (`ClientMenuiserieRepository`). Bind dans
 * `Menuiserie360ServiceProvider::register()` à activer à ce moment.
 *
 * Note ADR-021 : ce contrat est INTERNE Menuiserie360. Il consomme le
 * contrat public `CustomerReader` d'Eshop360 — c'est l'unique import de
 * `Modules\Eshop360\Contracts\*` autorisé dans le namespace Menuiserie360
 * (à étendre selon besoins, mais toujours via `Contracts/`, jamais via
 * `Domain/*\Models\*`).
 */
interface ClientRepositoryContract
{
    /**
     * Récupère un client menuiserie par son ID (le même que l'ID
     * `eshop_customers.id` — pas de table de mapping, pivot via FK).
     *
     * Combine identité Eshop360 (CustomerDto) + données menuiserie propres.
     */
    public function find(int $instanceId, int $customerId): ?ClientMenuiserieDto;

    /**
     * Liste les clients menuiserie ayant déjà au moins un BC ou chantier
     * dans l'instance (filtre métier — pas tous les Customer Eshop360).
     *
     * @return iterable<ClientMenuiserieDto>
     */
    public function withMenuiserieHistory(int $instanceId): iterable;

    /**
     * Vérifie qu'un client peut être référencé par BC-Commercial / BC-Chantier
     * (existe côté Eshop360, instance correcte, actif).
     */
    public function canReference(int $instanceId, int $customerId): bool;

    /**
     * Convertit un CustomerDto Eshop360 en ClientMenuiserieDto enrichi
     * (utilisé par les Actions Menuiserie qui partent d'une lecture
     * `CustomerReader`).
     *
     * @param  array<string, mixed>|null  $menuiserieAttributes  Attributs propres
     *                                                           menuiserie (préférences, historique) — null si non disponibles.
     */
    public function fromCustomerDto(CustomerDto $customer, ?array $menuiserieAttributes = null): ClientMenuiserieDto;
}
