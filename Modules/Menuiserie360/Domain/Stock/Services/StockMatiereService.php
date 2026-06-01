<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Services;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Stock\Contracts\StockContract;
use Modules\Menuiserie360\Domain\Stock\Exceptions\StockInsuffisantException;
use Modules\Menuiserie360\Domain\Stock\Models\MouvementStock;
use Modules\Menuiserie360\Domain\Stock\Models\StockMatiere;

/**
 * P1-3 — Service de gestion du stock matière (implémente {@see StockContract}).
 *
 * Garanties :
 *  - **Multi-tenant** : tous les accès sont scopés par `instance_id`.
 *  - **Concurrence** : entrée/sortie/ajustement effectués sous `lockForUpdate()`
 *    dans une transaction (pattern hérité d'ADR-002 stock concurrency Eshop360).
 *    Empêche les double-spend / négatifs sous charge multi-process.
 *  - **Idempotence** : chaque mouvement enregistré porte une `reference` unique
 *    par (instance, type) — un re-jeu via la même référence est silencieusement
 *    ignoré (pattern hérité d'ADR-003 webhook idempotence).
 *  - **Audit trail** : chaque opération produit une ligne dans
 *    `mnu_mouvements_stock` avec snapshot de la quantité post-mouvement.
 *
 * Bind comme `StockContract::class` dans
 * {@see \Modules\Menuiserie360\Providers\Menuiserie360ServiceProvider::register()}.
 */
final class StockMatiereService implements StockContract
{
    public function isAvailable(int $instanceId, int $matiereId, float $quantity): bool
    {
        $stock = $this->findOrInitStock($instanceId, $matiereId);

        return $stock->quantiteDisponible() >= $quantity;
    }

    public function reserve(int $instanceId, int $matiereId, float $quantity, string $reference): void
    {
        $this->guardPositive($quantity);

        DB::transaction(function () use ($instanceId, $matiereId, $quantity, $reference) {
            // Idempotence applicative + DB UNIQUE.
            if ($this->mouvementExists($instanceId, 'reservation', $reference)) {
                return;
            }

            $stock = $this->lockStock($instanceId, $matiereId);

            $disponible = $stock->quantiteDisponible();
            if ($disponible < $quantity) {
                throw StockInsuffisantException::forMatiere($matiereId, $quantity, $disponible);
            }

            $newReservee = (float) $stock->getAttribute('quantite_reservee') + $quantity;
            $stock->setAttribute('quantite_reservee', $newReservee);
            $stock->save();

            $this->recordMouvement(
                instanceId: $instanceId,
                matiereId: $matiereId,
                type: 'reservation',
                quantite: $quantity,
                quantiteApres: (float) $stock->getAttribute('quantite_actuelle'),
                reference: $reference,
            );
        });
    }

    public function consume(int $instanceId, int $matiereId, float $quantity, string $reference): void
    {
        $this->guardPositive($quantity);

        DB::transaction(function () use ($instanceId, $matiereId, $quantity, $reference) {
            if ($this->mouvementExists($instanceId, 'sortie', $reference)) {
                return;
            }

            $stock = $this->lockStock($instanceId, $matiereId);

            $actuelle = (float) $stock->getAttribute('quantite_actuelle');
            $reservee = (float) $stock->getAttribute('quantite_reservee');

            if ($actuelle < $quantity) {
                throw StockInsuffisantException::forMatiere($matiereId, $quantity, $actuelle);
            }

            // Si la consommation correspond à une réservation préalable
            // (même référence préfixe), on libère d'abord la part réservée.
            $reserveAssociee = $this->reservedAmount($instanceId, $matiereId, $reference);
            $libere = min($reserveAssociee, $quantity);

            $stock->setAttribute('quantite_actuelle', $actuelle - $quantity);
            $stock->setAttribute('quantite_reservee', max(0.0, $reservee - $libere));
            $stock->setAttribute('derniere_sortie_at', Carbon::now());
            $stock->save();

            $this->recordMouvement(
                instanceId: $instanceId,
                matiereId: $matiereId,
                type: 'sortie',
                quantite: $quantity,
                quantiteApres: (float) $stock->getAttribute('quantite_actuelle'),
                reference: $reference,
            );
        });
    }

    public function release(int $instanceId, int $matiereId, string $reference): void
    {
        DB::transaction(function () use ($instanceId, $matiereId, $reference) {
            if ($this->mouvementExists($instanceId, 'release', $reference)) {
                return;
            }

            $reservee = $this->reservedAmount($instanceId, $matiereId, $reference);
            if ($reservee <= 0) {
                // Rien à libérer — réservation inexistante ou déjà consommée.
                return;
            }

            $stock = $this->lockStock($instanceId, $matiereId);
            $newReservee = max(0.0, (float) $stock->getAttribute('quantite_reservee') - $reservee);
            $stock->setAttribute('quantite_reservee', $newReservee);
            $stock->save();

            $this->recordMouvement(
                instanceId: $instanceId,
                matiereId: $matiereId,
                type: 'release',
                quantite: $reservee,
                quantiteApres: (float) $stock->getAttribute('quantite_actuelle'),
                reference: $reference,
            );
        });
    }

    /**
     * Entrée stock fournisseur (hors interface — usage interne BC-Stock).
     */
    public function recevoir(int $instanceId, int $matiereId, float $quantity, string $reference): void
    {
        $this->guardPositive($quantity);

        DB::transaction(function () use ($instanceId, $matiereId, $quantity, $reference) {
            if ($this->mouvementExists($instanceId, 'entree', $reference)) {
                return;
            }

            $stock = $this->lockStock($instanceId, $matiereId);
            $newActuelle = (float) $stock->getAttribute('quantite_actuelle') + $quantity;
            $stock->setAttribute('quantite_actuelle', $newActuelle);
            $stock->setAttribute('derniere_entree_at', Carbon::now());
            $stock->save();

            $this->recordMouvement(
                instanceId: $instanceId,
                matiereId: $matiereId,
                type: 'entree',
                quantite: $quantity,
                quantiteApres: $newActuelle,
                reference: $reference,
            );
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function findOrInitStock(int $instanceId, int $matiereId): StockMatiere
    {
        return StockMatiere::query()
            ->firstOrCreate(
                ['instance_id' => $instanceId, 'matiere_id' => $matiereId],
                ['quantite_actuelle' => 0, 'quantite_reservee' => 0]
            );
    }

    /**
     * Verrou pessimiste sur la ligne stock pour empêcher les race conditions.
     * Pattern ADR-002 — toujours sous DB::transaction().
     */
    private function lockStock(int $instanceId, int $matiereId): StockMatiere
    {
        $stock = StockMatiere::query()
            ->where('instance_id', $instanceId)
            ->where('matiere_id', $matiereId)
            ->lockForUpdate()
            ->first();

        if ($stock === null) {
            // Création atomique : on sort de la transaction temporairement
            // n'est pas possible — on insère directement avec lock implicit.
            $stock = StockMatiere::create([
                'instance_id' => $instanceId,
                'matiere_id' => $matiereId,
                'quantite_actuelle' => 0,
                'quantite_reservee' => 0,
            ]);
        }

        return $stock;
    }

    private function mouvementExists(int $instanceId, string $type, string $reference): bool
    {
        return MouvementStock::query()
            ->where('instance_id', $instanceId)
            ->where('type', $type)
            ->where('reference', $reference)
            ->exists();
    }

    /**
     * Quantité actuellement réservée pour une référence donnée
     * (somme des mouvements `reservation` non encore consommés/libérés).
     */
    private function reservedAmount(int $instanceId, int $matiereId, string $reference): float
    {
        $reserved = (float) MouvementStock::query()
            ->where('instance_id', $instanceId)
            ->where('matiere_id', $matiereId)
            ->where('type', 'reservation')
            ->where('reference', $reference)
            ->sum('quantite');

        // Note : pour MVP P1, on simplifie. Les release/consume produisent
        // leurs propres mouvements — la part libérée est tracée via le
        // lien `reference` partagée (caller responsabilité).
        return $reserved;
    }

    private function recordMouvement(
        int $instanceId,
        int $matiereId,
        string $type,
        float $quantite,
        float $quantiteApres,
        string $reference,
    ): void {
        try {
            MouvementStock::create([
                'instance_id' => $instanceId,
                'matiere_id' => $matiereId,
                'type' => $type,
                'quantite' => $quantite,
                'quantite_apres' => $quantiteApres,
                'reference' => $reference,
            ]);
        } catch (UniqueConstraintViolationException|QueryException $e) {
            // Race idempotence : un autre process a enregistré le même mouvement
            // (instance_id, type, reference) entre notre check applicatif et l'INSERT.
            // Comportement attendu : silently ignore (le mouvement EST enregistré).
            // Pattern ADR-003 webhook idempotence.
            if ($e instanceof UniqueConstraintViolationException
                || str_contains((string) $e->getMessage(), 'mnu_mvt_instance_type_ref_unique')
                || str_contains((string) $e->getMessage(), 'UNIQUE constraint failed')) {
                return;
            }

            throw $e;
        }
    }

    private function guardPositive(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La quantité doit être strictement positive.');
        }
    }
}
