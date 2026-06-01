<?php

namespace Modules\Eshop360\Tests\Unit;

use InvalidArgumentException;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\StockMovement;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Services\StockService;
use Modules\Eshop360\Tests\TestCase;

/**
 * R-001 — Garanties anti-race condition sur le stock.
 *
 * Ces tests valident la mitigation codée dans StockService::adjustStock()
 * (`lockForUpdate()` + `DB::transaction()` + refresh + guard `quantity >= 0`).
 *
 * Note : SQLite en mode :memory: (config phpunit.xml) ne reproduit PAS
 * une race réelle multi-processus (single-threaded) ; il ignore même la
 * clause FOR UPDATE. Ces tests valident donc :
 *   1. la STRUCTURE du code (le lock + la transaction sont bien présents),
 *   2. le COMPORTEMENT séquentiel sous lock (deux ventes successives ne
 *      peuvent pas produire un stock négatif),
 *   3. l'ISOLATION multi-tenant et le ROLLBACK transactionnel.
 *
 * Une validation complète sous concurrence réelle nécessite un stress-test
 * MySQL avec 2+ processus parallèles (voir ADR-002, hors scope des tests
 * unitaires Windows).
 */
final class StockServiceConcurrencyTest extends TestCase
{
    private function makeWarehouse(int $instanceId, string $code = 'WH-C'): Warehouse
    {
        return Warehouse::create([
            'instance_id' => $instanceId,
            'name' => 'Central',
            'code' => $code,
            'is_active' => true,
        ]);
    }

    private function makeProduct(int $instanceId, string $slug = 'race-product'): Product
    {
        return Product::create([
            'instance_id' => $instanceId,
            'name' => 'Race Product',
            'slug' => $slug.'-'.uniqid(),
            'sku' => 'RACE-'.uniqid(),
            'price' => 100,
            'cost_price' => 60,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);
    }

    /**
     * Test 1 — STRUCTURAL : le code de adjustStock() contient le lock
     * et la transaction. Verrouillage architectural : toute PR qui
     * retire `lockForUpdate()` ou `DB::transaction` échouera ici.
     */
    public function test_adjust_stock_source_uses_lock_for_update_within_transaction(): void
    {
        $source = file_get_contents(
            base_path('Modules/Eshop360/Services/StockService.php')
        );

        $this->assertNotFalse($source, 'StockService.php is readable.');

        // La transaction doit englober la mutation.
        $this->assertStringContainsString(
            'DB::transaction(function () use',
            $source,
            'adjustStock() doit muter le stock dans une DB::transaction().'
        );

        // Le lock pessimiste doit être posé avant toute lecture/écriture.
        $this->assertStringContainsString(
            'Stock::lockForUpdate()',
            $source,
            'adjustStock() doit utiliser Stock::lockForUpdate() avant read/write.'
        );

        // Le refresh après lock garantit qu'on lit la valeur fraîche.
        $this->assertStringContainsString(
            '$stock->refresh();',
            $source,
            'adjustStock() doit refresh() le stock sous le lock.'
        );

        // La garde quantity >= 0 doit être présente après refresh, avant increment.
        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*\$newQuantity\s*<\s*0\s*\)\s*\{\s*throw/s',
            $source,
            'adjustStock() doit lever une exception si newQuantity < 0.'
        );
    }

    /**
     * Test 2 — COMPORTEMENT SÉQUENTIEL : deux ventes successives sur
     * le même stock n'aboutissent jamais à un stock négatif.
     *
     * Simule un scénario "deux requêtes concurrentes" : stock=5,
     * chaque requête veut vendre 3. La première passe (stock=2),
     * la seconde voit le stock rafraîchi sous lock et refuse.
     */
    public function test_sequential_adjust_stock_never_produces_negative_quantity(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();
        $warehouse = $this->makeWarehouse($instance->id);
        $product = $this->makeProduct($instance->id);
        $service = app(StockService::class);

        // Stock initial = 5
        $service->adjustStock($product, $warehouse->id, 5, 'purchase');
        $this->assertSame(5, Stock::where('product_id', $product->id)->value('quantity'));

        // Première vente de 3 : OK, stock = 2
        $service->adjustStock($product, $warehouse->id, 3, 'sale');
        $this->assertSame(2, Stock::where('product_id', $product->id)->value('quantity'));

        // Seconde vente de 3 : doit échouer (stock courant = 2, pas 5)
        $thrown = false;
        try {
            $service->adjustStock($product, $warehouse->id, 3, 'sale');
        } catch (InvalidArgumentException $e) {
            $thrown = true;
            $this->assertStringContainsString('Insufficient stock', $e->getMessage());
        }
        $this->assertTrue(
            $thrown,
            'La seconde vente devait lever InvalidArgumentException (stock insuffisant).'
        );

        // Stock final garanti >= 0
        $finalQuantity = Stock::where('product_id', $product->id)->value('quantity');
        $this->assertSame(2, $finalQuantity);
        $this->assertGreaterThanOrEqual(0, $finalQuantity);
    }

    /**
     * Test 3 — ROLLBACK : quand la validation quantité échoue dans la
     * transaction, aucun StockMovement ne doit être persisté.
     */
    public function test_adjust_stock_rollback_on_insufficient_quantity(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();
        $warehouse = $this->makeWarehouse($instance->id);
        $product = $this->makeProduct($instance->id);
        $service = app(StockService::class);

        // Stock = 2
        $service->adjustStock($product, $warehouse->id, 2, 'purchase');
        $movementsBeforeAttempt = StockMovement::where('product_id', $product->id)->count();

        // Tentative de vente de 5 → doit throw
        try {
            $service->adjustStock($product, $warehouse->id, 5, 'sale');
            $this->fail('adjustStock(5) aurait dû lever InvalidArgumentException.');
        } catch (InvalidArgumentException $e) {
            // attendu
        }

        // Aucun nouveau StockMovement créé (rollback)
        $movementsAfterAttempt = StockMovement::where('product_id', $product->id)->count();
        $this->assertSame(
            $movementsBeforeAttempt,
            $movementsAfterAttempt,
            'Aucun StockMovement ne doit être créé lors du rollback transactionnel.'
        );

        // Stock inchangé
        $this->assertSame(
            2,
            Stock::where('product_id', $product->id)->value('quantity'),
            'Le stock doit rester inchangé après rollback.'
        );
    }

    /**
     * Test 4 — ISOLATION MULTI-TENANT : deux instances A et B ayant
     * chacune un stock distinct. adjustStock() dans l'instance A
     * ne doit jamais muter la ligne de l'instance B.
     */
    public function test_adjust_stock_isolates_by_instance(): void
    {
        // Instance A
        [$instanceA] = $this->setUpInstanceWithAdmin();
        $warehouseA = $this->makeWarehouse($instanceA->id, 'WH-A');
        $productA = $this->makeProduct($instanceA->id, 'product-a');
        $service = app(StockService::class);
        $service->adjustStock($productA, $warehouseA->id, 10, 'purchase');

        // Instance B (2e tenant, slug distinct pour éviter la collision UNIQUE)
        $instanceB = \App\Instances\Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
            'is_active' => true,
            'meta' => [],
        ]);
        CurrentInstance::set($instanceB);
        $warehouseB = Warehouse::create([
            'instance_id' => $instanceB->id,
            'name' => 'Central B',
            'code' => 'WH-B',
            'is_active' => true,
        ]);
        $productB = $this->makeProduct($instanceB->id, 'product-b');
        $service->adjustStock($productB, $warehouseB->id, 20, 'purchase');

        // Retour sur A : vente de 3
        CurrentInstance::set($instanceA);
        $service->adjustStock($productA, $warehouseA->id, 3, 'sale');

        // Lecture scalaire via value() — PHPStan-friendly (évite l'accès propriété dynamique)
        $quantityA = Stock::withoutGlobalScopes()
            ->where('instance_id', $instanceA->id)
            ->where('product_id', $productA->id)
            ->value('quantity');

        $quantityB = Stock::withoutGlobalScopes()
            ->where('instance_id', $instanceB->id)
            ->where('product_id', $productB->id)
            ->value('quantity');

        $this->assertSame(7, (int) $quantityA, 'Stock A = 10 - 3 = 7.');
        $this->assertSame(20, (int) $quantityB, 'Stock B intact à 20 (aucune interférence).');
    }

    /**
     * Test 5 — GARDE SÉMANTIQUE : une vente strictement supérieure au
     * stock disponible doit lever une exception avec un message clair,
     * même sur un stock fraîchement créé (firstOrCreate dans le lock).
     */
    public function test_adjust_stock_throws_on_first_sale_with_insufficient_stock(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();
        $warehouse = $this->makeWarehouse($instance->id);
        $product = $this->makeProduct($instance->id);
        $service = app(StockService::class);

        // Aucun stock préalable : firstOrCreate va créer avec quantity=0,
        // puis newQuantity = 0 + (-5) = -5 → throw.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/');

        $service->adjustStock($product, $warehouse->id, 5, 'sale');
    }
}
