<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyDto;
use Modules\Referentiel360\Contracts\Party\PartyReader;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Domain\Party\Models\PartyLink;

/**
 * Implémentation Eloquent du {@see PartyReader} (ADR-030).
 *
 * Unique endroit (avec Writer/Resolver) où les modèles `ref_*` sont importés.
 * Toutes les requêtes filtrent explicitement `instance_id` et bypassent le
 * global scope pour être indépendantes du CurrentInstance courant.
 */
final class EloquentPartyReader implements PartyReader
{
    public function find(int $instanceId, int $partyId): ?PartyDto
    {
        $party = Party::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->whereKey($partyId)
            ->first();

        return $party ? self::mapToDto($party) : null;
    }

    public function getByLink(int $instanceId, string $linkType, int $localId): ?PartyDto
    {
        $link = PartyLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $localId)
            ->first();

        if ($link === null) {
            return null;
        }

        return $this->find($instanceId, (int) $link->getAttribute('party_id'));
    }

    public function searchByIdentity(int $instanceId, ?string $email, ?string $phone): array
    {
        $email = PartyAttributesDto::normalizeEmail($email);
        $phone = PartyAttributesDto::normalizePhone($phone);

        if ($email === null && $phone === null) {
            return [];
        }

        $parties = Party::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->where(function ($q) use ($email, $phone): void {
                if ($email !== null) {
                    $q->orWhere('email', $email);
                }
                if ($phone !== null) {
                    $q->orWhere('phone', $phone);
                }
            })
            ->orderBy('id')
            ->get();

        return $parties->map(fn (Party $p): PartyDto => self::mapToDto($p))->all();
    }

    public static function mapToDto(Party $p): PartyDto
    {
        return new PartyDto(
            id: (int) $p->getAttribute('id'),
            instanceId: (int) $p->getAttribute('instance_id'),
            partyUid: (string) $p->getAttribute('party_uid'),
            isCustomer: (bool) $p->getAttribute('is_customer'),
            isSupplier: (bool) $p->getAttribute('is_supplier'),
            personType: (string) $p->getAttribute('person_type'),
            displayName: (string) $p->getAttribute('display_name'),
            firstName: self::str($p->getAttribute('first_name')),
            lastName: self::str($p->getAttribute('last_name')),
            legalName: self::str($p->getAttribute('legal_name')),
            email: self::str($p->getAttribute('email')),
            phone: self::str($p->getAttribute('phone')),
            phoneSecondary: self::str($p->getAttribute('phone_secondary')),
            address: self::str($p->getAttribute('address')),
            city: self::str($p->getAttribute('city')),
            country: (string) $p->getAttribute('country'),
            taxIdRccm: self::str($p->getAttribute('tax_id_rccm')),
            taxIdNif: self::str($p->getAttribute('tax_id_nif')),
            supplierCategory: self::str($p->getAttribute('supplier_category')),
            paymentTerms: self::str($p->getAttribute('payment_terms')),
            leadTimeDays: self::intOrNull($p->getAttribute('lead_time_days')),
            currency: self::str($p->getAttribute('currency')),
            isActive: (bool) $p->getAttribute('is_active'),
            notes: self::str($p->getAttribute('notes')),
            sourceModule: self::str($p->getAttribute('source_module')),
        );
    }

    private static function str(mixed $value): ?string
    {
        return $value !== null ? (string) $value : null;
    }

    private static function intOrNull(mixed $value): ?int
    {
        return $value !== null ? (int) $value : null;
    }
}
