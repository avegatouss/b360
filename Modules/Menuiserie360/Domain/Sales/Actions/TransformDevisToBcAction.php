<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Sales\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Commercial\Enums\StatutDevis;
use Modules\Menuiserie360\Domain\Commercial\Events\DevisAccepte;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Commercial\Models\LigneDevis;
use Modules\Menuiserie360\Domain\Sales\Enums\StatutBonCommande;
use Modules\Menuiserie360\Domain\Sales\Events\BonCommandeCreee;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommandeItem;
use RuntimeException;

/**
 * P2-3 — Transformation d'un Devis accepté en BonCommande.
 *
 * Atomique : transaction + numérotation BC atomique pattern ADR-006.
 * Idempotent : si devis déjà transformé (statut TRANSFORME), retourne
 * le BC existant sans en créer un nouveau.
 *
 * Dispatch :
 *   - DevisAccepte (avant le BC, déclenche création facture acompte)
 *   - BonCommandeCreee (après le BC, déclenche création OF)
 */
final class TransformDevisToBcAction
{
    private const MAX_NUMBER_ATTEMPTS = 5;

    private const PREFIX = 'BC';

    public function execute(Devis $devis, float $acomptePct = 30.0): BonCommande
    {
        // Idempotence : si déjà transformé, retourner le BC existant.
        if ($devis->getAttribute('statut') === StatutDevis::TRANSFORME->value) {
            $existing = BonCommande::query()
                ->where('instance_id', $devis->getAttribute('instance_id'))
                ->where('devis_id', $devis->getKey())
                ->first();

            if ($existing instanceof BonCommande) {
                return $existing;
            }
        }

        if (! in_array($devis->getAttribute('statut'), [StatutDevis::ACCEPTE->value, StatutDevis::VALIDE->value], true)) {
            throw new RuntimeException(
                "Le devis #{$devis->getKey()} doit être au statut accepte ou valide pour transformation (actuel : ".(string) $devis->getAttribute('statut').')'
            );
        }

        return DB::transaction(function () use ($devis, $acomptePct) {
            $bc = $this->createBcWithRetries($devis, $acomptePct);

            // Snapshot des lignes du devis dans bc_items
            $this->snapshotLines($devis, $bc);

            // Marquer le devis comme transformé
            $devis->setAttribute('statut', StatutDevis::TRANSFORME->value);
            $devis->save();

            // Events (DevisAccepte d'abord — listener Finance crée acompte
            // qui sera lié au BC via facture_acompte_id par le listener)
            DevisAccepte::dispatch(
                (int) $devis->getAttribute('instance_id'),
                (int) $devis->getKey(),
                (int) $bc->getKey(),
                (int) $devis->getAttribute('client_id'),
                (float) $bc->getAttribute('montant_ttc'),
                $acomptePct,
            );

            BonCommandeCreee::dispatch(
                (int) $bc->getAttribute('instance_id'),
                (int) $bc->getKey(),
                (int) $devis->getKey(),
                (int) $bc->getAttribute('client_id'),
            );

            return $bc;
        });
    }

    private function createBcWithRetries(Devis $devis, float $acomptePct): BonCommande
    {
        for ($attempt = 0; $attempt < self::MAX_NUMBER_ATTEMPTS; $attempt++) {
            $numero = $this->computeNextNumero((int) $devis->getAttribute('instance_id'));

            try {
                return BonCommande::create([
                    'instance_id' => $devis->getAttribute('instance_id'),
                    'numero' => $numero,
                    'devis_id' => $devis->getKey(),
                    'client_id' => $devis->getAttribute('client_id'),
                    'statut' => StatutBonCommande::CREE->value,
                    'montant_ht' => $devis->getAttribute('montant_ht'),
                    'taux_tva' => $devis->getAttribute('taux_tva'),
                    'montant_tva' => $devis->getAttribute('montant_tva'),
                    'montant_ttc' => $devis->getAttribute('montant_ttc'),
                    'acompte_pct' => $acomptePct,
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

        throw new RuntimeException('TransformDevisToBcAction: max BC numbering attempts reached.');
    }

    private function computeNextNumero(int $instanceId): string
    {
        $year = (int) date('Y');
        $prefixYear = self::PREFIX.'-'.$year.'-';

        $last = BonCommande::query()
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

    private function snapshotLines(Devis $devis, BonCommande $bc): void
    {
        /** @var Collection<int, Model> $lignes */
        $lignes = LigneDevis::query()
            ->where('instance_id', $devis->getAttribute('instance_id'))
            ->where('devis_id', $devis->getKey())
            ->orderBy('ordre')
            ->get();

        foreach ($lignes as $ligne) {
            BonCommandeItem::create([
                'instance_id' => $bc->getAttribute('instance_id'),
                'bc_id' => $bc->getKey(),
                'ligne_devis_source_id' => $ligne->getKey(),
                'matiere_id' => $ligne->getAttribute('matiere_id'),
                'designation' => $ligne->getAttribute('designation'),
                'quantite' => $ligne->getAttribute('quantite'),
                'largeur_mm' => $ligne->getAttribute('largeur_mm'),
                'hauteur_mm' => $ligne->getAttribute('hauteur_mm'),
                'prix_unitaire_ht' => $ligne->getAttribute('prix_unitaire_ht'),
                'montant_ht' => $ligne->getAttribute('montant_ht'),
                'cout_revient' => $ligne->getAttribute('cout_revient'),
                'ordre' => $ligne->getAttribute('ordre'),
            ]);
        }
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        $msg = (string) $e->getMessage();

        return str_contains($msg, 'UNIQUE constraint failed')
            || str_contains($msg, 'mnu_bc_instance_numero_unique')
            || str_contains($msg, 'Duplicate entry');
    }
}
