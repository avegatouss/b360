<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Production\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Production\Enums\StatutOrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabricationLigne;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommandeItem;
use RuntimeException;

/**
 * P2-6 — Transformation d'un BonCommande en OrdreFabrication.
 *
 * Listener cible : à brancher sur BonCommandeCreee event au démarrage des
 * controllers (P2-B). Pour P2-A, l'Action est utilisée directement.
 *
 * Idempotente par bc_id : si OF déjà existant pour ce BC, retourne l'existant.
 */
final class TransformBcToOfAction
{
    private const MAX_NUMBER_ATTEMPTS = 5;

    private const PREFIX = 'OF';

    public function execute(BonCommande $bc): OrdreFabrication
    {
        // Idempotence : un seul OF par BC.
        $existing = OrdreFabrication::query()
            ->where('instance_id', $bc->getAttribute('instance_id'))
            ->where('bc_id', $bc->getKey())
            ->first();

        if ($existing instanceof OrdreFabrication) {
            return $existing;
        }

        return DB::transaction(function () use ($bc) {
            $of = $this->createOfWithRetries($bc);
            $this->snapshotItemsToLignes($bc, $of);

            return $of;
        });
    }

    private function createOfWithRetries(BonCommande $bc): OrdreFabrication
    {
        for ($attempt = 0; $attempt < self::MAX_NUMBER_ATTEMPTS; $attempt++) {
            $numero = $this->computeNextNumero((int) $bc->getAttribute('instance_id'));

            try {
                return OrdreFabrication::create([
                    'instance_id' => $bc->getAttribute('instance_id'),
                    'numero' => $numero,
                    'bc_id' => $bc->getKey(),
                    'statut' => StatutOrdreFabrication::EN_ATTENTE->value,
                ]);
            } catch (UniqueConstraintViolationException) {
                continue;
            } catch (QueryException $e) {
                if ($this->isUniqueViolation($e)) {
                    continue;
                }

                throw $e;
            }
        }

        throw new RuntimeException('TransformBcToOfAction: max OF numbering attempts reached.');
    }

    private function computeNextNumero(int $instanceId): string
    {
        $year = (int) date('Y');
        $prefixYear = self::PREFIX.'-'.$year.'-';

        $last = OrdreFabrication::query()
            ->where('instance_id', $instanceId)
            ->where('numero', 'like', $prefixYear.'%')
            ->orderByDesc('numero')
            ->value('numero');

        $sequence = 1;
        if ($last !== null) {
            $parts = explode('-', (string) $last);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('%s%04d', $prefixYear, $sequence);
    }

    private function snapshotItemsToLignes(BonCommande $bc, OrdreFabrication $of): void
    {
        /** @var Collection<int, Model> $items */
        $items = BonCommandeItem::query()
            ->where('instance_id', $bc->getAttribute('instance_id'))
            ->where('bc_id', $bc->getKey())
            ->orderBy('ordre')
            ->get();

        foreach ($items as $item) {
            OrdreFabricationLigne::create([
                'instance_id' => $of->getAttribute('instance_id'),
                'of_id' => $of->getKey(),
                'bc_item_id' => $item->getKey(),
                'designation' => $item->getAttribute('designation'),
                'quantite' => $item->getAttribute('quantite'),
                'largeur_mm' => $item->getAttribute('largeur_mm'),
                'hauteur_mm' => $item->getAttribute('hauteur_mm'),
                'statut_ligne' => 'a_fabriquer',
            ]);
        }
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        $msg = (string) $e->getMessage();

        return str_contains($msg, 'UNIQUE constraint failed')
            || str_contains($msg, 'mnu_of_instance_numero_unique')
            || str_contains($msg, 'Duplicate entry');
    }
}
