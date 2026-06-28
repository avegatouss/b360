<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Party\Services;

use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Domain\Party\Models\PartyLink;

/**
 * ADR-030 (L2 sensible) — Déduplication conservatrice des tiers, scopée
 * `instance_id`. Aucune fusion cross-instance possible (toutes les requêtes
 * filtrent explicitement l'instance et bypassent le global scope pour rester
 * indépendantes du CurrentInstance courant).
 *
 * Ordre de priorité (premier qui matche gagne) :
 *   1. Lien fiable existant (un linkType a déjà un party_id).
 *   2. Email normalisé (lower/trim) non vide.
 *   3. Téléphone normalisé (digits) non vide.
 *   4. RCCM ou NIF non vide.
 *   5. Sinon → aucun match (nouveau party).
 *
 * Collisions ambiguës (>1 candidat distinct sur une clé) ⇒ PAS de fusion auto :
 * renvoie un MatchResult `review`.
 */
final class PartyMatcher
{
    public function match(int $instanceId, string $linkType, PartyAttributesDto $attrs): MatchResult
    {
        // 1. Lien fiable existant : cet objet local est déjà rattaché.
        $existingLink = PartyLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $attrs->localId)
            ->first();

        if ($existingLink !== null) {
            return MatchResult::matched((int) $existingLink->getAttribute('party_id'), 'link');
        }

        // 2. Email normalisé.
        $email = $attrs->normalizedEmail();
        if ($email !== null) {
            $result = $this->matchByColumn($instanceId, 'email', $email, 'email');
            if ($result !== null) {
                return $result;
            }
        }

        // 3. Téléphone normalisé.
        $phone = $attrs->normalizedPhone();
        if ($phone !== null) {
            $result = $this->matchByColumn($instanceId, 'phone', $phone, 'phone');
            if ($result !== null) {
                return $result;
            }
        }

        // 4. RCCM puis NIF.
        $rccm = $this->normalizeTaxId($attrs->taxIdRccm);
        if ($rccm !== null) {
            $result = $this->matchByColumn($instanceId, 'tax_id_rccm', $rccm, 'rccm');
            if ($result !== null) {
                return $result;
            }
        }

        $nif = $this->normalizeTaxId($attrs->taxIdNif);
        if ($nif !== null) {
            $result = $this->matchByColumn($instanceId, 'tax_id_nif', $nif, 'nif');
            if ($result !== null) {
                return $result;
            }
        }

        // 5. Aucun signal → nouveau party.
        return MatchResult::none();
    }

    /**
     * Cherche les parties distinctes ayant cette valeur de colonne.
     * 0 → null (pas de match) ; 1 → matched ; >1 → review (collision).
     */
    private function matchByColumn(int $instanceId, string $column, string $value, string $rule): ?MatchResult
    {
        $ids = Party::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where($column, $value)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return null;
        }

        if ($ids->count() > 1) {
            return MatchResult::review(
                $rule,
                "Collision sur {$column}='{$value}' : ".$ids->count().' parties candidates'
            );
        }

        return MatchResult::matched($ids->first(), $rule);
    }

    private function normalizeTaxId(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
