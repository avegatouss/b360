<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-031 / Lot 3 — Résolution du `party_id` via le PartyReader (lien interne au
 * module, autorisé). CA consolidé par tiers cross-module.
 */
final class FinancePartyResolutionTest extends TestCase
{
    private int $instanceId;

    private FinanceWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
        $this->writer = app(FinanceWriter::class);
    }

    public function test_party_id_resolved_when_party_link_exists(): void
    {
        // Crée un party golden + lien mnu.client/77 (comme le ferait le Lot 1.a).
        $party = app(PartyWriter::class)->upsertFromModule(
            $this->instanceId,
            'mnu.client',
            new PartyAttributesDto(localId: 77, isCustomer: true, displayName: 'Client Test'),
        );

        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 600,
            documentNumber: 'MNU-FAC-2026-0010',
            amountTtc: '50000.00',
            partyLinkType: 'mnu.client',
            partyLocalId: 77,
            sourceModule: 'menuiserie',
        ));

        $this->assertSame($party->id, $dto->partyId);
    }

    public function test_party_id_null_when_no_link(): void
    {
        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 601,
            documentNumber: 'MNU-FAC-2026-0011',
            amountTtc: '50000.00',
            partyLinkType: 'mnu.client',
            partyLocalId: 999, // aucun party lié
            sourceModule: 'menuiserie',
        ));

        $this->assertNull($dto->partyId);
    }

    public function test_party_id_null_when_no_party_attributes(): void
    {
        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 602,
            documentNumber: 'MNU-FAC-2026-0012',
            amountTtc: '50000.00',
            sourceModule: 'menuiserie',
        ));

        $this->assertNull($dto->partyId);
    }

    /**
     * MAJEUR 5 — pas de downgrade : un party résolu lors d'un 1er push doit être
     * CONSERVÉ si un 2e push (refresh) ne parvient plus à le re-résoudre
     * (lien party absent). Le miroir ne repasse JAMAIS party_id à null.
     */
    public function test_party_id_not_downgraded_to_null_on_refresh(): void
    {
        $party = app(PartyWriter::class)->upsertFromModule(
            $this->instanceId,
            'mnu.client',
            new PartyAttributesDto(localId: 88, isCustomer: true, displayName: 'Client Stable'),
        );

        // 1er push : party résolu et lié.
        $first = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 610,
            documentNumber: 'MNU-FAC-2026-0020',
            amountTtc: '50000.00',
            paidAmount: '10000.00',
            partyLinkType: 'mnu.client',
            partyLocalId: 88,
            sourceModule: 'menuiserie',
        ));
        $this->assertSame($party->id, $first->partyId);

        // 2e push (refresh paiement) où la résolution courante échoue
        // (partyLocalId pointant un id sans party) : l'ancien party_id est conservé.
        $second = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 610,
            documentNumber: 'MNU-FAC-2026-0020',
            amountTtc: '50000.00',
            paidAmount: '50000.00',
            partyLinkType: 'mnu.client',
            partyLocalId: 9999, // aucun party lié → résolution null
            sourceModule: 'menuiserie',
        ));

        $this->assertSame($first->id, $second->id);
        $this->assertSame($party->id, $second->partyId, 'party_id conservé (pas de downgrade vers null)');
    }
}
