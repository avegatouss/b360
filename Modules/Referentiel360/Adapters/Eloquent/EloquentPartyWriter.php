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
 * puis rafraîchit le golden record selon la politique « refresh-if-present »
 * (R-505) : une valeur source non-vide écrase l'existant ; une source vide/null
 * ne vide jamais un champ déjà renseigné. Les rôles restent en OR (jamais
 * rétrogradés) et `source_module` conserve l'origine du golden. Le lien est
 * garanti (idempotent) et PartyUpserted est émis.
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
     * Fusion « refresh-if-present » (R-505) : promeut les rôles (OR) et écrase
     * chaque champ identité dès que la valeur source est non-vide ; une source
     * vide/null laisse l'existant intact (jamais de remise à vide). `country`,
     * `display_name`/identité : aucune exception, mais comme la source vide ne
     * touche rien, `display_name` n'est jamais vidé. `source_module` n'est
     * volontairement pas rafraîchi (on conserve l'origine du golden).
     */
    private function mergeInto(Party $party, PartyAttributesDto $attrs): void
    {
        $party->is_customer = (bool) $party->is_customer || $attrs->isCustomer;
        $party->is_supplier = (bool) $party->is_supplier || $attrs->isSupplier;

        $this->refreshIfPresent($party, 'display_name', $attrs->displayName);
        $this->refreshIfPresent($party, 'first_name', $attrs->firstName);
        $this->refreshIfPresent($party, 'last_name', $attrs->lastName);
        $this->refreshIfPresent($party, 'legal_name', $attrs->legalName);
        $this->refreshIfPresent($party, 'email', $attrs->normalizedEmail());
        $this->refreshIfPresent($party, 'phone', $attrs->normalizedPhone());
        $this->refreshIfPresent($party, 'phone_secondary', $attrs->phoneSecondary);
        $this->refreshIfPresent($party, 'address', $attrs->address);
        $this->refreshIfPresent($party, 'city', $attrs->city);
        $this->refreshIfPresent($party, 'country', $attrs->country);
        $this->refreshIfPresent($party, 'tax_id_rccm', $attrs->taxIdRccm);
        $this->refreshIfPresent($party, 'tax_id_nif', $attrs->taxIdNif);
        $this->refreshIfPresent($party, 'supplier_category', $attrs->supplierCategory);
        $this->refreshIfPresent($party, 'payment_terms', $attrs->paymentTerms);
        $this->refreshIfPresent($party, 'currency', $attrs->currency);
        $this->refreshIfPresent($party, 'notes', $attrs->notes);

        if ($attrs->leadTimeDays !== null) {
            $party->lead_time_days = $attrs->leadTimeDays;
        }

        if ($party->isDirty()) {
            $party->save();
        }
    }

    /**
     * Écrase la colonne si la valeur source est présente (non-null et non-vide
     * après trim) ; sinon laisse l'existant intact. Cœur de la politique R-505.
     */
    private function refreshIfPresent(Party $party, string $column, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }
        $party->setAttribute($column, $value);
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
