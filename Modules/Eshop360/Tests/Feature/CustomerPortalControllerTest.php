<?php

namespace Modules\Eshop360\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;

final class CustomerPortalControllerTest extends TestCase
{
    public function test_customer_portal_can_submit_channel_online_order(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $user = $this->makeUser('portal-customer@test.com');
        TeamContext::set(0);
        $this->attachMembership($user->id, $instance->id);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'code' => 'CUS-PORTAL-001',
            'name' => 'Client Portail',
            'email' => $user->email,
            'address' => 'Abidjan',
            'is_active' => true,
        ]);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Canal Portail',
            'slug' => 'canal-portail',
            'code' => 'CH-PORTAL',
            'buy_rate' => 0.20,
            'margin_rate' => 0.13,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
            'is_active' => true,
        ]);

        $product = $this->makeProduct($instance->id, [
            'name' => 'Paracetamol Portal',
            'slug' => 'paracetamol-portal',
            'sku' => 'PORTAL-001',
            'price' => 120,
            'pght' => 80,
        ]);

        ChannelProductPrice::create([
            'channel_id' => $channel->id,
            'product_id' => $product->id,
            'sale_price' => 96,
            'is_manual_override' => true,
        ]);

        $this->actingAs($user)
            ->get(route('eshop360.portal.catalog', ['slug' => $instance->slug]))
            ->assertOk()
            ->assertSee('Paracetamol Portal');

        $this->actingAs($user)
            ->post(route('eshop360.portal.cart.add', ['slug' => $instance->slug]), [
                'product_id' => $product->id,
                'quantity' => 2,
                'channel_id' => $channel->id,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('eshop360.portal.cart', ['slug' => $instance->slug]))
            ->assertOk()
            ->assertSee('Paracetamol Portal');

        $response = $this->actingAs($user)
            ->post(route('eshop360.portal.checkout', ['slug' => $instance->slug]), [
                'delivery_address' => 'Boulevard principal',
                'notes' => 'Livraison rapide',
            ]);

        $response->assertRedirect();

        $onlineOrder = OnlineOrder::query()->latest()->first();

        $this->assertNotNull($onlineOrder);
        $this->assertSame($customer->id, $onlineOrder->customer_id);
        $this->assertSame($channel->id, $onlineOrder->channel_id);
        $this->assertTrue($onlineOrder->isChannelOrder());
        $this->assertSame('pending_validation', $onlineOrder->status);
        $this->assertEquals(192.00, (float) $onlineOrder->total);

        $this->actingAs($user)
            ->get(route('eshop360.portal.orders.show', ['slug' => $instance->slug, 'onlineOrder' => $onlineOrder]))
            ->assertOk()
            ->assertSee($onlineOrder->reference);
    }

    public function test_customer_portal_can_submit_channel_order_and_confirm_reception(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $user = $this->makeUser('channel-customer@test.com');
        TeamContext::set(0);
        $this->attachMembership($user->id, $instance->id);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'code' => 'CUS-PORTAL-002',
            'name' => 'Client Canal',
            'email' => $user->email,
            'address' => 'Yopougon',
            'is_active' => true,
        ]);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Grossiste B',
            'slug' => 'grossiste-b',
            'code' => 'CH-B',
            'buy_rate' => 0.25,
            'margin_rate' => 0.15,
            'debt_share' => 0.33,
            'channel_share' => 0.33,
            'owner_share' => 0.34,
            'is_active' => true,
        ]);

        $product = $this->makeProduct($instance->id, [
            'name' => 'Amoxicilline',
            'slug' => 'amoxicilline',
            'sku' => 'PORTAL-002',
            'price' => 150,
            'pght' => 100,
        ]);

        $this->actingAs($user)
            ->post(route('eshop360.portal.cart.add', ['slug' => $instance->slug]), [
                'product_id' => $product->id,
                'quantity' => 3,
                'channel_id' => $channel->id,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('eshop360.portal.checkout', ['slug' => $instance->slug]), [
                'delivery_address' => 'Zone industrielle',
            ])
            ->assertRedirect();

        $onlineOrder = OnlineOrder::query()->latest()->firstOrFail();

        $this->assertSame($customer->id, $onlineOrder->customer_id);
        $this->assertSame($channel->id, $onlineOrder->channel_id);
        $this->assertTrue($onlineOrder->isChannelOrder());
        $this->assertEquals(375.00, (float) $onlineOrder->total);

        $onlineOrder->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('eshop360.portal.orders.received', ['slug' => $instance->slug, 'onlineOrder' => $onlineOrder]))
            ->assertRedirect();

        $this->assertDatabaseHas('eshop_online_orders', [
            'id' => $onlineOrder->id,
            'status' => 'received',
        ]);
    }

    public function test_customer_portal_hides_orders_from_other_customers(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $firstUser = $this->makeUser('first-customer@test.com');
        $secondUser = $this->makeUser('second-customer@test.com');
        TeamContext::set(0);
        $this->attachMembership($firstUser->id, $instance->id);
        $this->attachMembership($secondUser->id, $instance->id);

        Customer::create([
            'instance_id' => $instance->id,
            'user_id' => $firstUser->id,
            'code' => 'CUS-PORTAL-003',
            'name' => 'Premier client',
            'email' => $firstUser->email,
            'is_active' => true,
        ]);

        $secondCustomer = Customer::create([
            'instance_id' => $instance->id,
            'user_id' => $secondUser->id,
            'code' => 'CUS-PORTAL-004',
            'name' => 'Deuxieme client',
            'email' => $secondUser->email,
            'is_active' => true,
        ]);

        $order = OnlineOrder::create([
            'instance_id' => $instance->id,
            'customer_id' => $secondCustomer->id,
            'reference' => 'ONL-PRIVATE',
            'status' => 'pending_validation',
            'subtotal' => 100,
            'tax_amount' => 0,
            'total' => 100,
        ]);

        $this->actingAs($firstUser)
            ->get(route('eshop360.portal.orders.show', ['slug' => $instance->slug, 'onlineOrder' => $order]))
            ->assertNotFound();
    }

    private function attachMembership(int $userId, int $instanceId): void
    {
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instanceId,
            'user_id' => $userId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeProduct(int $instanceId, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'instance_id' => $instanceId,
            'name' => 'Produit Portail',
            'slug' => 'produit-portail',
            'sku' => 'PORTAL-SKU',
            'price' => 100,
            'cost_price' => 60,
            'pght' => 90,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'boite',
            'min_quantity' => 1,
            'alert_quantity' => 1,
            'is_active' => true,
        ], $overrides));
    }
}
