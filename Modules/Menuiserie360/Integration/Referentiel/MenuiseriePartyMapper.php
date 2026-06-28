<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Menuiserie360\Domain\Purchasing\Models\Fournisseur;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;

/**
 * Lot 1.a (ADR-030) — Mapper unique Menuiserie360 → PartyAttributesDto.
 *
 * Partagé par les observers (push temps réel sur created/updated) et par les
 * PartySource (backfill). Convertit un modèle local Menuiserie360 en DTO neutre
 * d'entrée pour le golden record Referentiel360. Aucune logique d'écriture ici.
 *
 * N'importe QUE la surface publique `Contracts\Party\*` de Referentiel360.
 */
final class MenuiseriePartyMapper
{
    /**
     * ClientMenuiserie → PartyAttributesDto (rôle customer).
     *
     * `type` (string libre côté modèle : 'particulier' | 'entreprise') alimente
     * directement personType. displayName = raison_sociale sinon "prenom nom".
     */
    public function fromClient(ClientMenuiserie $client): PartyAttributesDto
    {
        $raisonSociale = self::nullableString($client->getAttribute('raison_sociale'));
        $prenom = self::nullableString($client->getAttribute('prenom'));
        $nom = self::nullableString($client->getAttribute('nom'));

        $displayName = $raisonSociale ?? trim(implode(' ', array_filter([$prenom, $nom], static fn (?string $v): bool => $v !== null)));

        return new PartyAttributesDto(
            localId: (int) $client->getKey(),
            isCustomer: true,
            personType: self::nullableString($client->getAttribute('type')) ?? 'particulier',
            displayName: $displayName,
            firstName: $prenom,
            lastName: $nom,
            legalName: $raisonSociale,
            email: self::nullableString($client->getAttribute('email')),
            phone: self::nullableString($client->getAttribute('telephone_principal')),
            phoneSecondary: self::nullableString($client->getAttribute('telephone_secondaire')),
            address: self::nullableString($client->getAttribute('adresse')),
            city: self::nullableString($client->getAttribute('ville')),
            country: self::nullableString($client->getAttribute('pays')) ?? 'CI',
            taxIdRccm: self::nullableString($client->getAttribute('rccm')),
            taxIdNif: self::nullableString($client->getAttribute('nif')),
            notes: self::nullableString($client->getAttribute('notes_menuiserie')),
            sourceModule: 'menuiserie',
        );
    }

    /**
     * Fournisseur → PartyAttributesDto (rôle supplier).
     *
     * personType forcé à 'entreprise' (un fournisseur Menuiserie est toujours une
     * structure). categorie est un BackedEnum (CategorieFournisseur) → ->value.
     */
    public function fromFournisseur(Fournisseur $fournisseur): PartyAttributesDto
    {
        $categorie = $fournisseur->getAttribute('categorie');
        $supplierCategory = $categorie instanceof \BackedEnum
            ? (string) $categorie->value
            : self::nullableString($categorie);

        $delai = $fournisseur->getAttribute('delai_livraison_jours');

        return new PartyAttributesDto(
            localId: (int) $fournisseur->getKey(),
            isSupplier: true,
            personType: 'entreprise',
            displayName: self::nullableString($fournisseur->getAttribute('nom_commercial')) ?? '',
            legalName: self::nullableString($fournisseur->getAttribute('raison_sociale')),
            email: self::nullableString($fournisseur->getAttribute('email')),
            phone: self::nullableString($fournisseur->getAttribute('telephone_principal')),
            phoneSecondary: self::nullableString($fournisseur->getAttribute('telephone_secondaire')),
            address: self::nullableString($fournisseur->getAttribute('adresse')),
            city: self::nullableString($fournisseur->getAttribute('ville')),
            country: self::nullableString($fournisseur->getAttribute('pays')) ?? 'CI',
            taxIdRccm: self::nullableString($fournisseur->getAttribute('rccm')),
            taxIdNif: self::nullableString($fournisseur->getAttribute('nif')),
            supplierCategory: $supplierCategory,
            paymentTerms: self::nullableString($fournisseur->getAttribute('conditions_paiement')),
            leadTimeDays: $delai !== null ? (int) $delai : null,
            currency: self::nullableString($fournisseur->getAttribute('devise')),
            notes: self::nullableString($fournisseur->getAttribute('notes')),
            sourceModule: 'menuiserie',
        );
    }

    /**
     * Normalise une valeur d'attribut en ?string : null si vide après trim.
     */
    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = is_string($value) ? trim($value) : trim((string) $value);

        return $string === '' ? null : $string;
    }
}
