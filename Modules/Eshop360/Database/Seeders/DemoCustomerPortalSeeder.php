<?php

namespace Modules\Eshop360\Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\OnlineOrderItem;
use Modules\Eshop360\Models\Product;

/**
 * Demo seeder: 4 key customers with user accounts + online orders
 * covering the full lifecycle (pending → validated → shipping → delivered → received).
 */
final class DemoCustomerPortalSeeder
{
    public function run(int $instanceId): void
    {
        $products = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->limit(10)
            ->get();

        if ($products->count() < 3) {
            return;
        }

        $portalCustomers = $this->seedPortalCustomers($instanceId);
        $this->seedPortalOrders($instanceId, $portalCustomers, $products);
    }

    public function reset(int $instanceId): void
    {
        // Remove online orders
        $orderIds = OnlineOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('reference', 'like', 'DEMO-PORTAL-%')
            ->pluck('id');

        OnlineOrderItem::whereIn('online_order_id', $orderIds)->delete();
        OnlineOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('reference', 'like', 'DEMO-PORTAL-%')
            ->delete();

        // Remove portal customers and their user accounts
        $customers = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('code', 'like', 'DEMO-PORTAL-%')
            ->get();

        foreach ($customers as $c) {
            if ($c->user_id) {
                DB::connection('system')->table('instance_user')
                    ->where('user_id', $c->user_id)
                    ->where('instance_id', $instanceId)
                    ->delete();
                User::where('id', $c->user_id)->forceDelete();
            }
        }

        Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('code', 'like', 'DEMO-PORTAL-%')
            ->delete();
    }

    private function seedPortalCustomers(int $instanceId): array
    {
        $data = [
            [
                'code' => 'DEMO-PORTAL-001',
                'name' => 'Pharmacie du Plateau',
                'email' => 'demo-pharma-plateau@b360.test',
                'phone' => '+225 07 01 01 01',
                'address' => 'Avenue Terrasson de Fougères, Plateau',
                'city' => 'Abidjan',
                'country' => 'CI',
                'company_name' => 'Pharmacie du Plateau SARL',
                'credit_limit' => 5000000,
            ],
            [
                'code' => 'DEMO-PORTAL-002',
                'name' => 'Grossiste Santé Plus',
                'email' => 'demo-sante-plus@b360.test',
                'phone' => '+225 07 02 02 02',
                'address' => 'Zone Industrielle Vridi',
                'city' => 'Abidjan',
                'country' => 'CI',
                'company_name' => 'Santé Plus Distribution',
                'credit_limit' => 15000000,
            ],
            [
                'code' => 'DEMO-PORTAL-003',
                'name' => 'Centre Médical Espoir',
                'email' => 'demo-centre-espoir@b360.test',
                'phone' => '+225 07 03 03 03',
                'address' => 'Boulevard de la Paix, Cocody',
                'city' => 'Abidjan',
                'country' => 'CI',
                'company_name' => 'Centre Médical Espoir',
                'credit_limit' => 3000000,
            ],
            [
                'code' => 'DEMO-PORTAL-004',
                'name' => 'ONG MediAfrica',
                'email' => 'demo-medafrica@b360.test',
                'phone' => '+225 07 04 04 04',
                'address' => 'Rue du Commerce, Marcory',
                'city' => 'Abidjan',
                'country' => 'CI',
                'company_name' => 'ONG MediAfrica International',
                'credit_limit' => 8000000,
            ],
        ];

        $result = [];

        foreach ($data as $d) {
            // Create user account for portal access
            $user = User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'full_name' => $d['name'],
                    'password' => 'password',
                    'is_active' => true,
                    'is_blocked' => false,
                ]
            );

            // Add membership to instance
            DB::connection('system')->table('instance_user')->updateOrInsert(
                ['user_id' => $user->id, 'instance_id' => $instanceId],
                ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
            );

            // Create customer linked to user
            $customer = Customer::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'code' => $d['code']],
                array_merge($d, [
                    'instance_id' => $instanceId,
                    'user_id' => $user->id,
                    'is_active' => true,
                    'wallet_balance' => 0,
                    'loyalty_points' => rand(50, 500),
                ])
            );

            $result[] = $customer;
        }

        return $result;
    }

    private function seedPortalOrders(int $instanceId, array $customers, $products): void
    {
        // Full lifecycle: each customer gets orders at different stages
        $orders = [
            // Customer 0 (Pharmacie du Plateau): 1 received + 1 pending
            [
                'customer_index' => 0,
                'reference' => 'DEMO-PORTAL-ORD-001',
                'status' => 'received',
                'delivery_address' => 'Avenue Terrasson de Fougères, Plateau, Abidjan',
                'notes' => '[DEMO] Commande reçue et confirmée par le client',
                'confirmed_at' => now()->subDays(10),
                'delivered_at' => now()->subDays(5),
                'received_at' => now()->subDays(4),
                'items' => [
                    ['index' => 0, 'qty' => 20],
                    ['index' => 1, 'qty' => 15],
                    ['index' => 2, 'qty' => 10],
                ],
            ],
            [
                'customer_index' => 0,
                'reference' => 'DEMO-PORTAL-ORD-002',
                'status' => 'pending_validation',
                'delivery_address' => 'Avenue Terrasson de Fougères, Plateau, Abidjan',
                'delivery_notes' => 'Livrer avant 14h',
                'notes' => '[DEMO] Nouvelle commande en attente de validation Saphir',
                'items' => [
                    ['index' => 3, 'qty' => 30],
                    ['index' => 4, 'qty' => 10],
                ],
            ],

            // Customer 1 (Grossiste Santé Plus): 1 validated + 1 shipping
            [
                'customer_index' => 1,
                'reference' => 'DEMO-PORTAL-ORD-003',
                'status' => 'validated',
                'delivery_address' => 'Zone Industrielle Vridi, Abidjan',
                'notes' => '[DEMO] Commande validée par Saphir, en attente de préparation',
                'confirmed_at' => now()->subDays(2),
                'items' => [
                    ['index' => 0, 'qty' => 50],
                    ['index' => 5, 'qty' => 25],
                    ['index' => 6, 'qty' => 40],
                ],
            ],
            [
                'customer_index' => 1,
                'reference' => 'DEMO-PORTAL-ORD-004',
                'status' => 'shipping',
                'delivery_address' => 'Zone Industrielle Vridi, Abidjan',
                'delivery_notes' => 'Quai de réception B, badge obligatoire',
                'notes' => '[DEMO] En cours de livraison',
                'confirmed_at' => now()->subDays(5),
                'delivered_at' => null,
                'items' => [
                    ['index' => 1, 'qty' => 100],
                    ['index' => 7, 'qty' => 60],
                ],
            ],

            // Customer 2 (Centre Médical Espoir): 1 preparing + 1 delivered (awaiting confirmation)
            [
                'customer_index' => 2,
                'reference' => 'DEMO-PORTAL-ORD-005',
                'status' => 'preparing',
                'delivery_address' => 'Boulevard de la Paix, Cocody, Abidjan',
                'notes' => '[DEMO] En préparation dans l\'entrepôt',
                'confirmed_at' => now()->subDays(1),
                'items' => [
                    ['index' => 2, 'qty' => 5],
                    ['index' => 8, 'qty' => 8],
                ],
            ],
            [
                'customer_index' => 2,
                'reference' => 'DEMO-PORTAL-ORD-006',
                'status' => 'delivered',
                'delivery_address' => 'Boulevard de la Paix, Cocody, Abidjan',
                'notes' => '[DEMO] Livrée, en attente de confirmation réception par le client',
                'confirmed_at' => now()->subDays(7),
                'delivered_at' => now()->subDays(1),
                'items' => [
                    ['index' => 0, 'qty' => 12],
                    ['index' => 3, 'qty' => 8],
                    ['index' => 9, 'qty' => 15],
                ],
            ],

            // Customer 3 (ONG MediAfrica): 1 big order pending + 1 invoiced
            [
                'customer_index' => 3,
                'reference' => 'DEMO-PORTAL-ORD-007',
                'status' => 'pending_validation',
                'delivery_address' => 'Rue du Commerce, Marcory, Abidjan',
                'delivery_notes' => 'Urgent - programme vaccination',
                'notes' => '[DEMO] Grosse commande ONG en attente de validation',
                'items' => [
                    ['index' => 0, 'qty' => 200],
                    ['index' => 1, 'qty' => 150],
                    ['index' => 5, 'qty' => 100],
                    ['index' => 6, 'qty' => 75],
                ],
            ],
            [
                'customer_index' => 3,
                'reference' => 'DEMO-PORTAL-ORD-008',
                'status' => 'invoiced',
                'delivery_address' => 'Rue du Commerce, Marcory, Abidjan',
                'notes' => '[DEMO] Commande terminée et facturée',
                'confirmed_at' => now()->subDays(20),
                'delivered_at' => now()->subDays(14),
                'received_at' => now()->subDays(13),
                'items' => [
                    ['index' => 2, 'qty' => 80],
                    ['index' => 4, 'qty' => 60],
                ],
            ],
        ];

        foreach ($orders as $o) {
            $customer = $customers[$o['customer_index']];
            $itemRows = [];
            $subtotal = 0;

            foreach ($o['items'] as $item) {
                $product = $products->values()->get($item['index'] % $products->count());
                if (!$product) continue;

                $unitPrice = (float) ($product->selling_price ?? $product->sale_price ?? $product->price ?? 0);
                $lineTotal = $unitPrice * $item['qty'];
                $subtotal += $lineTotal;

                $itemRows[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            }

            $taxAmount = round($subtotal * 0.18, 2);

            $orderData = [
                'instance_id' => $instanceId,
                'customer_id' => $customer->id,
                'reference' => $o['reference'],
                'status' => $o['status'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => round($subtotal + $taxAmount, 2),
                'delivery_address' => $o['delivery_address'],
                'delivery_notes' => $o['delivery_notes'] ?? null,
                'notes' => $o['notes'] ?? null,
                'confirmed_at' => $o['confirmed_at'] ?? null,
                'delivered_at' => $o['delivered_at'] ?? null,
                'received_at' => $o['received_at'] ?? null,
            ];

            $onlineOrder = OnlineOrder::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'reference' => $o['reference']],
                $orderData
            );

            OnlineOrderItem::where('online_order_id', $onlineOrder->id)->delete();
            foreach ($itemRows as $row) {
                OnlineOrderItem::create(array_merge($row, ['online_order_id' => $onlineOrder->id]));
            }
        }
    }
}
