<?php

declare(strict_types=1);

namespace Modules\Eshop360\Contracts\Customer;

/**
 * Contrat public de lecture client (CRM) Eshop360 (ADR-021 §1).
 *
 * Consommé par les modules métier futurs (Menuiserie360, etc.) via
 * injection de dépendance. L'implémentation par défaut est
 * {@see \Modules\Eshop360\Adapters\Eloquent\EloquentCustomerReader}.
 *
 * Lecture seule. Toute mutation (création, mise à jour, wallet) reste
 * la responsabilité d'Eshop360 et passe par les services internes
 * (`CustomerService`, `FinanceService` pour le wallet — zone L1
 * `PROTECTED_AREAS.md`).
 */
interface CustomerReader
{
    /**
     * Récupère un client par son ID au sein d'une instance.
     *
     * @return CustomerDto|null null si le client n'existe pas ou appartient
     *                          à une autre instance.
     */
    public function findCustomer(int $instanceId, int $customerId): ?CustomerDto;

    /**
     * Récupère un client par son code (référence métier — ex. CDF-001).
     */
    public function findCustomerByCode(int $instanceId, string $code): ?CustomerDto;

    /**
     * Vérifie l'existence d'un client (utile pour validation FK applicative
     * avant de créer une référence depuis un module L4).
     */
    public function customerExists(int $instanceId, int $customerId): bool;
}
