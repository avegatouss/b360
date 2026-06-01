<?php

namespace Modules\Eshop360\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Finance\Models\AccountTransaction;
use Modules\Eshop360\Domain\Finance\Models\FneInvoice;
use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Eshop360\Domain\Finance\Models\Payment;
use Modules\Eshop360\Domain\Inventory\Models\StockMovement;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Domain\Sales\Models\OrderItem;
use Modules\Eshop360\Tests\TestCase;

/**
 * R-101 S12 morph stability guard.
 *
 * Verifies that the centralised morph map in Eshop360ServiceProvider keeps
 * `<x>_type` columns stored with the **legacy FQN** (`Modules\Eshop360\Models\<X>`),
 * and that morphTo() resolves those legacy FQNs back to the canonical
 * `Modules\Eshop360\Domain\<Sub>\Models\<X>` class.
 *
 * If this test fails, production rows with legacy FQN will silently start
 * pointing nowhere — a regression we cannot afford on L1 surfaces.
 */
final class MorphStabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Critical morph targets: legacy FQN => canonical class.
     */
    private const CRITICAL_TARGETS = [
        'Modules\Eshop360\Models\Order' => Order::class,
        'Modules\Eshop360\Models\OrderItem' => OrderItem::class,
        'Modules\Eshop360\Models\Invoice' => Invoice::class,
        'Modules\Eshop360\Models\Payment' => Payment::class,
        'Modules\Eshop360\Models\StockMovement' => StockMovement::class,
        'Modules\Eshop360\Models\FneInvoice' => FneInvoice::class,
        'Modules\Eshop360\Models\AccountTransaction' => AccountTransaction::class,
    ];

    public function test_canonical_classes_report_legacy_morph_class(): void
    {
        foreach (self::CRITICAL_TARGETS as $legacyFqn => $canonical) {
            $instance = new $canonical;
            $this->assertSame(
                $legacyFqn,
                $instance->getMorphClass(),
                "Canonical {$canonical} must report legacy FQN {$legacyFqn} as morph class"
            );
        }
    }

    public function test_morphed_model_resolves_legacy_fqn_to_canonical(): void
    {
        foreach (self::CRITICAL_TARGETS as $legacyFqn => $canonical) {
            $resolved = Relation::getMorphedModel($legacyFqn);
            $this->assertSame(
                $canonical,
                $resolved,
                "Legacy FQN {$legacyFqn} must resolve to canonical {$canonical}"
            );
        }
    }

    public function test_payment_payable_type_persists_as_legacy_fqn_for_order(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'MORPH-CUST-1',
            'name' => 'Morph Stability Customer',
            'email' => 'morph-stability@test.local',
        ]);

        $order = Order::create([
            'instance_id' => $instance->id,
            'customer_id' => $customer->id,
            'order_number' => 'MORPH-ORDER-1',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 100,
            'total' => 100,
            'paid_amount' => 100,
            'due_amount' => 0,
        ]);

        $payment = Payment::create([
            'instance_id' => $instance->id,
            'payable_type' => $order->getMorphClass(),
            'payable_id' => $order->id,
            'amount' => 100,
            'method' => 'cash',
            'reference' => 'MORPH-PAY-1',
        ]);

        $this->assertSame(
            'Modules\Eshop360\Models\Order',
            $payment->getAttribute('payable_type'),
            'Payment.payable_type must be the legacy FQN for an Order morph parent'
        );

        $reloaded = Payment::find($payment->getKey());
        $this->assertNotNull($reloaded);
        $payable = $reloaded->payable;
        $this->assertInstanceOf(Order::class, $payable);
        $this->assertSame($order->getKey(), $payable->getKey());
    }

    public function test_morph_map_covers_all_extracted_canonicals(): void
    {
        $expected = 88;
        $map = Relation::morphMap();

        $eshopEntries = array_filter(
            $map,
            fn ($key) => str_starts_with($key, 'Modules\Eshop360\Models\\'),
            ARRAY_FILTER_USE_KEY
        );

        $this->assertCount(
            $expected,
            $eshopEntries,
            "Expected {$expected} legacy FQN keys in morph map; got ".count($eshopEntries)
        );
    }
}
