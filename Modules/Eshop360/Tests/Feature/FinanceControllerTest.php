<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Domain\Promotions\Models\GiftCard;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Tests\TestCase;

final class FinanceControllerTest extends TestCase
{
    // ─── FinanceService::debitWallet ───────────────────────────────────

    public function test_debit_wallet_decrements_balance_and_returns_deducted(): void
    {
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-000001',
            'name' => 'Test Client',
            'wallet_balance' => 150.00,
            'is_active' => true,
        ]);

        $service = app(FinanceService::class);
        $deducted = $service->debitWallet($customer, 50.0);

        $this->assertEquals(50.0, $deducted);
        $this->assertEquals(100.00, (float) $customer->fresh()->wallet_balance);
    }

    public function test_debit_wallet_caps_at_available_balance(): void
    {
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-000002',
            'name' => 'Low Balance Client',
            'wallet_balance' => 30.00,
            'is_active' => true,
        ]);

        $service = app(FinanceService::class);
        $deducted = $service->debitWallet($customer, 100.0);

        $this->assertEquals(30.0, $deducted);
        $this->assertEquals(0.0, (float) $customer->fresh()->wallet_balance);
    }

    // ─── FinanceService::useGiftCard ───────────────────────────────────

    public function test_use_gift_card_records_negative_topup_and_depletes_card(): void
    {
        $instance = $this->makeRootInstance();
        $this->makeRootSuperAdmin($instance);

        $card = GiftCard::create([
            'instance_id' => $instance->id,
            'code' => 'TESTCARD0001',
            'amount' => 100.00,
            'balance' => 100.00,
            'status' => 'active',
        ]);

        $service = app(FinanceService::class);
        $deducted = $service->useGiftCard($card, 100.0);

        $this->assertEquals(100.0, $deducted);
        $this->assertEquals('depleted', $card->fresh()->status);
        $this->assertEquals(0.0, (float) $card->fresh()->balance);

        // A negative topup record should exist
        $this->assertDatabaseHas('eshop_gift_card_topups', [
            'gift_card_id' => $card->id,
            'amount' => -100.0,
        ]);
    }

    // ─── FinanceService::profitAndLoss ─────────────────────────────────

    public function test_profit_and_loss_includes_cogs_and_gross_margin(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Main',
            'code' => 'MAIN-001',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Widget',
            'slug' => 'widget',
            'sku' => 'WID-001',
            'price' => 100.0,
            'cost_price' => 60.0,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'pcs',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
        ]);

        // Create a completed order with 2 items
        $order = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'SAL-0001',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'subtotal' => 200.0,
            'total' => 200.0,
            'paid_amount' => 200.0,
            'due_amount' => 0,
            'source' => 'manual',
            'biller_id' => $user->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 100.0,
            'total' => 200.0,
        ]);

        $this->actingAs($user);

        $service = app(FinanceService::class);
        $data = $service->profitAndLoss(
            $instance->id,
            now()->startOfMonth()->toDateString(),
            now()->toDateString(),
            false
        );

        $this->assertEquals(200.0, $data['total_revenue']);
        $this->assertEquals(120.0, $data['cogs']); // 2 * 60
        $this->assertEquals(80.0, $data['gross_profit']); // 200 - 120
        $this->assertEquals(40.0, $data['gross_margin']); // 80/200*100
        // Net profit = revenue - expenses (COGS shown as info only)
        $this->assertEquals(200.0, $data['net_profit']); // no purchases/expenses in this test
    }

    // ─── Checkout: wallet payment rejected when balance insufficient ───

    public function test_checkout_rejects_wallet_payment_when_balance_insufficient(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Product A',
            'slug' => 'product-a',
            'sku' => 'PA-001',
            'price' => 500.0,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'pcs',
            'min_quantity' => 0,
            'alert_quantity' => 1,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-WAL-001',
            'name' => 'Wallet Client',
            'wallet_balance' => 100.00, // insufficient for 500
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $instanceId = $instance->id;
        session(["eshop_cart_instance_{$instanceId}" => [
            'pa-001' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => 1,
                'unit_price' => 500.0,
                'total' => 500.0,
                'tax_rate' => 0,
            ],
        ]]);

        $response = $this->post(route('eshop360.checkout.process', $instance->slug), [
            'customer_id' => $customer->id,
            'payment_method' => 'wallet',
            'paid_amount' => 500.0,
        ]);

        $response->assertSessionHasErrors(['payment_method']);
        $this->assertDatabaseMissing('eshop_orders', ['customer_id' => $customer->id]);
    }
}
