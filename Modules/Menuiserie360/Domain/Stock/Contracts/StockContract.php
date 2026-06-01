<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Contracts;

/**
 * Contrat interne BC-Stock Menuiserie360.
 *
 * Spec v1.3 §1.2 — frontière BC-Stock : "BC-Stock est autonome : il expose
 * une interface StockContract consommée par Production et Commercial."
 *
 * Définition v1.0 — méthodes minimum pour l'autonomie. À enrichir au fil de
 * P1..P2 (BesoinMatiereService, alertes, transferts inter-stocks).
 *
 * Implémentation à coder en P1-3 (`StockMatiereService`). Bind dans
 * `Menuiserie360ServiceProvider::register()` à activer à ce moment.
 *
 * Note ADR-021 : ce contrat est INTERNE Menuiserie360 (consommé uniquement
 * par les BC-Production et BC-Commercial du même module). Il ne fait pas
 * partie de la surface publique exposée à d'autres modules L4.
 */
interface StockContract
{
    /**
     * Vérifie qu'une matière première est disponible en quantité demandée
     * dans une instance.
     */
    public function isAvailable(int $instanceId, int $matiereId, float $quantity): bool;

    /**
     * Réserve une quantité (avant fabrication). Réduit le stock disponible
     * sans toucher au stock physique. Idempotent par référence métier.
     */
    public function reserve(int $instanceId, int $matiereId, float $quantity, string $reference): void;

    /**
     * Consomme une réservation lors de la fabrication effective. Décrémente
     * le stock physique et libère la réservation associée.
     */
    public function consume(int $instanceId, int $matiereId, float $quantity, string $reference): void;

    /**
     * Annule une réservation (cas de BC annulé avant fabrication).
     */
    public function release(int $instanceId, int $matiereId, string $reference): void;
}
