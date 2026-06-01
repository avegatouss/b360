<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Eshop360\Services\InvoiceService;
use Modules\Eshop360\Tests\TestCase;

/**
 * R-202 — Atomicité de la numérotation des factures Eshop360.
 *
 * `InvoiceService::createFromItems()` génère un numéro via
 * `generateInvoiceNumber()` (suffixe aléatoire 6 caractères) puis insère
 * sous la contrainte UNIQUE `(instance_id, invoice_number)` ajoutée par
 * la migration P0 `2026_04_04_100002_add_unique_order_and_invoice_numbers`.
 * En cas de collision le `QueryException 1062` est rattrapé et un nouveau
 * numéro est régénéré, jusqu'à `MAX_NUMBER_ATTEMPTS` tentatives.
 *
 * Voir ADR-006-invoice-numbering-atomicity.
 */
final class InvoiceNumberAtomicityTest extends TestCase
{
    /**
     * Test 1 — STRUCTURAL : le pattern de retry + UNIQUE constraint +
     * QueryException handling est bien en place dans le source.
     * Toute PR qui le retirerait casse ce test.
     */
    public function test_invoice_service_source_has_retry_loop_and_query_exception_handling(): void
    {
        $source = (string) file_get_contents(
            base_path('Modules/Eshop360/Services/InvoiceService.php')
        );

        // Constante limite de retry (évite un livelock)
        $this->assertStringContainsString(
            'MAX_NUMBER_ATTEMPTS',
            $source,
            'InvoiceService doit définir une constante MAX_NUMBER_ATTEMPTS.'
        );

        // Boucle de retry
        $this->assertMatchesRegularExpression(
            '/for\s*\(\s*\$attempt\s*=\s*1\s*;\s*\$attempt\s*<=\s*self::MAX_NUMBER_ATTEMPTS/',
            $source,
            'createFromItems() doit itérer jusqu\'à MAX_NUMBER_ATTEMPTS.'
        );

        // Import + catch de QueryException
        $this->assertStringContainsString(
            'use Illuminate\\Database\\QueryException;',
            $source,
            'Import de QueryException requis.'
        );
        $this->assertStringContainsString(
            'catch (QueryException',
            $source,
            'createFromItems() doit catch QueryException (collision UNIQUE).'
        );

        // Re-throw si MAX atteint ou autre erreur (pas 1062)
        $this->assertStringContainsString(
            '1062',
            $source,
            'Le retry doit filtrer spécifiquement le code MySQL 1062 (duplicate).'
        );

        // Régénération d'un nouveau numéro entre tentatives
        $this->assertStringContainsString(
            'generateInvoiceNumber()',
            $source,
            'createFromItems() doit régénérer un numéro entre tentatives.'
        );

        // DB transaction englobante
        $this->assertStringContainsString(
            'DB::transaction',
            $source,
            'createFromItems() doit tourner dans DB::transaction().'
        );
    }

    /**
     * Test 2 — DB-LEVEL : la contrainte UNIQUE sur
     * `(instance_id, invoice_number)` est bien active et refuse deux
     * invoices avec même paire dans la même instance.
     */
    public function test_database_rejects_duplicate_invoice_number_within_instance(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $sharedNumber = 'INV-TEST-DUP-'.uniqid();

        Invoice::create([
            'instance_id' => $instance->id,
            'invoice_number' => $sharedNumber,
            'status' => 'draft',
            'subtotal' => 100,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'paid_amount' => 0,
            'due_amount' => 100,
        ]);

        // La 2e insertion avec même paire doit être refusée par la DB.
        $this->expectException(\Illuminate\Database\QueryException::class);

        Invoice::create([
            'instance_id' => $instance->id,
            'invoice_number' => $sharedNumber,
            'status' => 'draft',
            'subtotal' => 200,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 200,
            'paid_amount' => 0,
            'due_amount' => 200,
        ]);
    }

    /**
     * Test 3 — COMPORTEMENT : createFromItems() produit des numéros
     * distincts sur des appels consécutifs (happy path, pas de collision
     * en conditions normales).
     */
    public function test_create_from_items_produces_distinct_invoice_numbers(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $service = app(InvoiceService::class);

        $invoice1 = $service->createFromItems(
            [[
                'product_id' => null,
                'description' => 'Item A',
                'quantity' => 1,
                'unit_price' => 100,
                'discount' => 0,
                'tax' => 0,
                'tax_rate' => 0,
                'total' => 100,
            ]],
            [
                'instance_id' => $instance->id,
                'status' => 'draft',
            ]
        );

        $invoice2 = $service->createFromItems(
            [[
                'product_id' => null,
                'description' => 'Item B',
                'quantity' => 1,
                'unit_price' => 200,
                'discount' => 0,
                'tax' => 0,
                'tax_rate' => 0,
                'total' => 200,
            ]],
            [
                'instance_id' => $instance->id,
                'status' => 'draft',
            ]
        );

        $number1 = (string) Invoice::query()->where('id', $invoice1->id)->value('invoice_number');
        $number2 = (string) Invoice::query()->where('id', $invoice2->id)->value('invoice_number');

        $this->assertNotEmpty($number1);
        $this->assertNotEmpty($number2);
        $this->assertNotSame($number1, $number2, 'Deux invoices successives doivent avoir des numéros distincts.');
        $this->assertStringStartsWith('INV-', $number1);
        $this->assertStringStartsWith('INV-', $number2);
    }
}
