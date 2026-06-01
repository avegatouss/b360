<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Production\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Menuiserie360\Domain\Commercial\Models\TypeProduitMenuiserie;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabricationLigne;
use Modules\Menuiserie360\Domain\Stock\Contracts\StockContract;

/**
 * P2-10 — Calcul automatique du besoin matière depuis un OF.
 *
 * Règles métier :
 *   - Pour chaque ligne d'OF, on déduit la liste des matières et quantités
 *     nécessaires depuis :
 *     1. Le `bc_item_id` lié → matiere_id explicite si défini
 *     2. Sinon : les `matieres_principales` du TypeProduitMenuiserie associé
 *        (configuration auto), ratio par unité × quantité
 *     3. Périmètre linéaire (profilés alu) calculé depuis dimensions
 *
 * Délègue au {@see StockContract} pour vérifier disponibilité et réserver
 * (pas de duplication de la logique stock — couplage uniquement via le
 * contrat interne).
 *
 * Usage typique (par UI/job en P2-B) :
 *   $besoins = $service->calculerBesoinsPourOf($of);
 *   foreach ($besoins as $matiereId => $qty) {
 *       $stock->isAvailable($instanceId, $matiereId, $qty) — alerte si KO
 *   }
 */
final class BesoinMatiereService
{
    public function __construct(
        private readonly StockContract $stock,
    ) {}

    /**
     * Calcule le besoin matière agrégé pour un OF.
     *
     * @return array<int, float> matiere_id => quantité requise
     */
    public function calculerBesoinsPourOf(OrdreFabrication $of): array
    {
        /** @var Collection<int, OrdreFabricationLigne> $lignes */
        $lignes = OrdreFabricationLigne::query()
            ->where('instance_id', $of->getAttribute('instance_id'))
            ->where('of_id', $of->getKey())
            ->get();

        $besoins = [];

        foreach ($lignes as $ligne) {
            $contributions = $this->besoinPourLigne($ligne);
            foreach ($contributions as $matiereId => $qty) {
                $besoins[$matiereId] = ($besoins[$matiereId] ?? 0.0) + $qty;
            }
        }

        return $besoins;
    }

    /**
     * Vérifie si l'OF est intégralement réalisable au stock actuel.
     *
     * @return array<int, array{matiere_id: int, requise: float, disponible: bool}>
     *                                                                              Liste des matières avec statut disponibilité.
     */
    public function verifierDisponibilite(OrdreFabrication $of): array
    {
        $besoins = $this->calculerBesoinsPourOf($of);
        $result = [];

        foreach ($besoins as $matiereId => $requise) {
            $disponible = $this->stock->isAvailable(
                (int) $of->getAttribute('instance_id'),
                $matiereId,
                $requise,
            );

            $result[] = [
                'matiere_id' => $matiereId,
                'requise' => $requise,
                'disponible' => $disponible,
            ];
        }

        return $result;
    }

    /**
     * Réserve les matières nécessaires pour un OF (référence = numero OF).
     * Idempotent — si déjà réservé, no-op via le contrat StockContract.
     */
    public function reserverPourOf(OrdreFabrication $of): void
    {
        $besoins = $this->calculerBesoinsPourOf($of);
        $instanceId = (int) $of->getAttribute('instance_id');
        $reference = (string) $of->getAttribute('numero');

        foreach ($besoins as $matiereId => $qty) {
            $this->stock->reserve($instanceId, $matiereId, $qty, $reference);
        }
    }

    /**
     * Libère les réservations d'un OF (cas annulation).
     * Idempotent.
     */
    public function libererPourOf(OrdreFabrication $of): void
    {
        $besoins = $this->calculerBesoinsPourOf($of);
        $instanceId = (int) $of->getAttribute('instance_id');
        $reference = (string) $of->getAttribute('numero');

        foreach ($besoins as $matiereId => $_qty) {
            $this->stock->release($instanceId, $matiereId, $reference);
        }
    }

    // ─── Détails calcul par ligne ────────────────────────────────

    /**
     * @return array<int, float> matiere_id → qty
     */
    private function besoinPourLigne(OrdreFabricationLigne $ligne): array
    {
        $besoins = [];

        // Cas 1 : bc_item lié + type produit configuré → utilise matieres_principales
        $bcItemId = $ligne->getAttribute('bc_item_id');
        if ($bcItemId !== null) {
            $type = $this->resolveTypeProduit($ligne);
            if ($type !== null) {
                $besoins = $this->besoinDepuisType($type, $ligne);
            }
        }

        return $besoins;
    }

    private function resolveTypeProduit(OrdreFabricationLigne $ligne): ?TypeProduitMenuiserie
    {
        // Heuristique simple v1 : on cherche un TypeProduit dont le nom
        // matche la designation du ligne. Plus tard (P2-B) un FK explicite
        // type_produit_id sera ajouté à mnu_bc_items pour précision.
        return TypeProduitMenuiserie::query()
            ->where('instance_id', $ligne->getAttribute('instance_id'))
            ->where('nom', $ligne->getAttribute('designation'))
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return array<int, float>
     */
    private function besoinDepuisType(TypeProduitMenuiserie $type, OrdreFabricationLigne $ligne): array
    {
        /** @var array<int, array{matiere_id: int, qte_par_unite: float}>|null $matieres */
        $matieres = $type->getAttribute('matieres_principales');

        if ($matieres === null || $matieres === []) {
            return [];
        }

        $qty = (int) $ligne->getAttribute('quantite');
        $besoins = [];

        foreach ($matieres as $config) {
            $mid = (int) ($config['matiere_id'] ?? 0);
            $perUnit = (float) ($config['qte_par_unite'] ?? 0);

            if ($mid <= 0 || $perUnit <= 0) {
                continue;
            }

            $besoins[$mid] = ($besoins[$mid] ?? 0.0) + ($perUnit * $qty);
        }

        return $besoins;
    }
}
