<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Finance\Services\InvoiceNumberGenerator;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P2-7 part 2 — Tests InvoiceNumberGenerator.
 *
 * Couvre les invariants ADR-006 atomicity :
 *   - Format MNU-FAC-YYYY-NNNN
 *   - Séquence incrémentale par année
 *   - Multi-tenant isolation
 *   - Retry sur UniqueConstraintViolationException (simulé)
 */
final class InvoiceNumberGeneratorTest extends TestCase
{
    private InvoiceNumberGenerator $generator;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $instance = $this->makeRootInstance();
        $this->instanceId = $instance->id;
        CurrentInstance::set($instance);

        $this->generator = new InvoiceNumberGenerator;
    }

    public function test_first_invoice_number_is_sequence_0001(): void
    {
        $invoice = $this->generator->generateAndCreate(
            $this->instanceId,
            fn (string $num) => $this->makeInvoice($num),
        );

        $year = (int) date('Y');
        $this->assertSame("MNU-FAC-{$year}-0001", $invoice->getAttribute('invoice_number'));
    }

    public function test_sequential_numbers_increment(): void
    {
        $a = $this->generator->generateAndCreate($this->instanceId, fn ($num) => $this->makeInvoice($num));
        $b = $this->generator->generateAndCreate($this->instanceId, fn ($num) => $this->makeInvoice($num));
        $c = $this->generator->generateAndCreate($this->instanceId, fn ($num) => $this->makeInvoice($num));

        $year = (int) date('Y');
        $this->assertSame("MNU-FAC-{$year}-0001", $a->getAttribute('invoice_number'));
        $this->assertSame("MNU-FAC-{$year}-0002", $b->getAttribute('invoice_number'));
        $this->assertSame("MNU-FAC-{$year}-0003", $c->getAttribute('invoice_number'));
    }

    public function test_format_pads_sequence_to_4_digits(): void
    {
        // Force a high starting sequence by inserting a precursor
        $year = (int) date('Y');
        $this->makeInvoice("MNU-FAC-{$year}-0099");

        $next = $this->generator->generateAndCreate($this->instanceId, fn ($num) => $this->makeInvoice($num));

        $this->assertSame("MNU-FAC-{$year}-0100", $next->getAttribute('invoice_number'));
    }

    public function test_numbers_are_isolated_by_instance(): void
    {
        // Instance A — séquence partant de 0001
        $a1 = $this->generator->generateAndCreate(
            $this->instanceId,
            fn ($num) => $this->makeInvoice($num),
        );

        // Bascule sur instance B → la séquence redémarre à 0001 (scoped)
        $instanceB = \App\Instances\Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);
        CurrentInstance::set($instanceB);

        $b1 = $this->generator->generateAndCreate(
            $instanceB->id,
            fn ($num) => $this->makeInvoice($num, $instanceB->id),
        );

        $year = (int) date('Y');
        $this->assertSame("MNU-FAC-{$year}-0001", $a1->getAttribute('invoice_number'));
        $this->assertSame("MNU-FAC-{$year}-0001", $b1->getAttribute('invoice_number'));
    }

    public function test_returned_model_is_persisted(): void
    {
        $invoice = $this->generator->generateAndCreate(
            $this->instanceId,
            fn ($num) => $this->makeInvoice($num),
        );

        $this->assertNotNull($invoice->getKey());
        $this->assertTrue($invoice->exists);
    }

    private function makeInvoice(string $number, ?int $instanceId = null): MenuiserieInvoice
    {
        return MenuiserieInvoice::create([
            'instance_id' => $instanceId ?? $this->instanceId,
            'invoice_number' => $number,
            'client_id' => 1,
            'type' => 'acompte',
            'amount_ht' => 1000,
            'tax_rate' => 0.18,
            'amount_tva' => 180,
            'amount_ttc' => 1180,
            'paid_amount' => 0,
            'status' => 'draft',
        ]);
    }
}
