<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyDto;
use Modules\Referentiel360\Contracts\Party\PartyResolver;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Domain\Party\Events\PartyUpserted;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Domain\Party\Models\PartyLink;

/**
 * Implémentation Eloquent du {@see PartyWriter} (ADR-030).
 *
 * `upsertFromModule` délègue la décision (réutiliser / créer) au PartyResolver,
 * puis met à jour le golden record de façon non destructive (ne vide jamais un
 * champ déjà renseigné), garantit le lien (idempotent), et émet PartyUpserted.
 */
final class EloquentPartyWriter implements PartyWriter
{
    public function __construct(private readonly PartyResolver $resolver) {}

    public function upsertFromModule(int $instanceId, string $linkType, PartyAttributesDto $attrs): PartyDto
    {
        return DB::transaction(function () use ($instanceId, $linkType, $attrs): PartyDto {
            $resolved = $this->resolver->resolve($instanceId, $linkType, $attrs);

            $created = false;

            if ($resolved === null) {
                $party = $this->createParty($instanceId, $attrs);
                $created = true;
            } else {
                $party = Party::query()
                    ->withoutGlobalScope(InstanceScope::class)
                    ->where('instance_id', $instanceId)
                    ->whereKey($resolved->id)
                    ->firstOrFail();
                $this->mergeInto($party, $attrs);
            }

            $this->ensureLink($instanceId, (int) $party->getKey(), $linkType, $attrs->localId);

            PartyUpserted::dispatch(
                $instanceId,
                (int) $party->getKey(),
                $linkType,
                $attrs->localId,
                $created,
            );

            return EloquentPartyReader::mapToDto($party->refresh());
        });
    }

    public function link(int $instanceId, int $partyId, string $linkType, int $localId): void
    {
        $this->ensureLink($instanceId, $partyId, $linkType, $localId);
    }

    private function createParty(int $instanceId, PartyAttributesDto $attrs): Party
    {
        $party = new Party;
        $party->instance_id = $instanceId;
        $party->is_customer = $attrs->isCustomer;
        $party->is_supplier = $attrs->isSupplier;
        $party->person_type = $attrs->personType;
        $party->display_name = $attrs->displayName !== '' ? $attrs->displayName : 'Tiers';
        $party->first_name = $attrs->firstName;
        $party->last_name = $attrs->lastName;
        $party->legal_name = $attrs->legalName;
        $party->email = $attrs->normalizedEmail();
        $party->phone = $attrs->normalizedPhone();
        $party->phone_secondary = $attrs->phoneSecondary;
        $party->address = $attrs->address;
        $party->city = $attrs->city;
        $party->country = $attrs->country;
        $party->tax_id_rccm = $attrs->taxIdRccm;
        $party->tax_id_nif = $attrs->taxIdNif;
        $party->supplier_category = $attrs->supplierCategory;
        $party->payment_terms = $attrs->paymentTerms;
        $party->lead_time_days = $attrs->leadTimeDays;
        $party->currency = $attrs->currency;
        $party->is_active = true;
        $party->notes = $attrs->notes;
        $party->source_module = $attrs->sourceModule;
        $party->save();

        return $party;
    }

    /**
     * Fusion non destructive : promeut les rôles (OR), complète les trous,
     * ne remplace jamais une valeur existante par un trou.
     */
    private function mergeInto(Party $party, PartyAttributesDto $attrs): void
    {
        $party->is_customer = (bool) $party->is_customer || $attrs->isCustomer;
        $party->is_supplier = (bool) $party->is_supplier || $attrs->isSupplier;

        $this->fillIfEmpty($party, 'display_name', $attrs->displayName !== '' ? $attrs->displayName : null);
        $this->fillIfEmpty($party, 'first_name', $attrs->firstName);
        $this->fillIfEmpty($party, 'last_name', $attrs->lastName);
        $this->fillIfEmpty($party, 'legal_name', $attrs->legalName);
        $this->fillIfEmpty($party, 'email', $attrs->normalizedEmail());
        $this->fillIfEmpty($party, 'phone', $attrs->normalizedPhone());
        $this->fillIfEmpty($party, 'phone_secondary', $attrs->phoneSecondary);
        $this->fillIfEmpty($party, 'address', $attrs->address);
        $this->fillIfEmpty($party, 'city', $attrs->city);
        $this->fillIfEmpty($party, 'tax_id_rccm', $attrs->taxIdRccm);
        $this->fillIfEmpty($party, 'tax_id_nif', $attrs->taxIdNif);
        $this->fillIfEmpty($party, 'supplier_category', $attrs->supplierCategory);
        $this->fillIfEmpty($party, 'payment_terms', $attrs->paymentTerms);
        $this->fillIfEmpty($party, 'currency', $attrs->currency);
        $this->fillIfEmpty($party, 'notes', $attrs->notes);

        if ($attrs->leadTimeDays !== null && $party->lead_time_days === null) {
            $party->lead_time_days = $attrs->leadTimeDays;
        }

        if ($party->isDirty()) {
            $party->save();
        }
    }

    private function fillIfEmpty(Party $party, string $column, ?string $value): void
    {
        if ($value === null) {
            return;
        }
        $current = $party->getAttribute($column);
        if ($current === null || $current === '') {
            $party->setAttribute($column, $value);
        }
    }

    private function ensureLink(int $instanceId, int $partyId, string $linkType, int $localId): void
    {
        $exists = PartyLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $localId)
            ->exists();

        if ($exists) {
            return;
        }

        PartyLink::create([
            'instance_id' => $instanceId,
            'party_id' => $partyId,
            'linkable_type' => $linkType,
            'linkable_id' => $localId,
        ]);
    }
}
