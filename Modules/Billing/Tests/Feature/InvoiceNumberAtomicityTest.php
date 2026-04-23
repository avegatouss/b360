<?php

namespace Modules\Billing\Tests\Feature;

use Illuminate\Database\QueryException;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\InvoiceManager;
use Modules\Billing\Tests\TestCase;

/**
 * R-202 — Atomicité de la numérotation des factures Billing.
 *
 * `InvoiceManager::generate()` s'appuie sur :
 *   1. `nextNumber()` qui lit MAX+1 (lecture non-atomique en soi)
 *   2. UNIQUE constraint globale sur `invoices.number` (migration
 *      `2026_03_07_000003_create_invoices_table`)
 *   3. Boucle de retry sur `QueryException 1062` avec régénération du
 *      numéro (introduite par R-202) jusqu'à MAX_NUMBER_ATTEMPTS.
 *
 * Voir ADR-006-invoice-numbering-atomicity.
 */
final class InvoiceNumberAtomicityTest extends TestCase
{
    /**
     * Test 1 — STRUCTURAL : le pattern retry + transaction + QueryException
     * est en place. Toute PR qui retirerait ces garanties casse ce test.
     */
    public function test_invoice_manager_source_has_retry_loop_and_transaction(): void
    {
        $source = (string) file_get_contents(
            base_path('Modules/Billing/Services/InvoiceManager.php')
        );

        $this->assertStringContainsString(
            'MAX_NUMBER_ATTEMPTS',
            $source,
            'InvoiceManager doit définir une limite de retry.'
        );

        $this->assertStringContainsString(
            'DB::transaction',
            $source,
            'generate() doit tourner dans DB::transaction.'
        );

        $this->assertStringContainsString(
            'use Illuminate\\Database\\QueryException;',
            $source,
            'QueryException doit être importée.'
        );

        $this->assertStringContainsString(
            'catch (QueryException',
            $source,
            'generate() doit catch QueryException pour absorber les collisions.'
        );

        $this->assertStringContainsString(
            '1062',
            $source,
            'Le retry doit filtrer le code MySQL 1062 (duplicate entry).'
        );

        $this->assertMatchesRegularExpression(
            '/for\s*\(\s*\$attempt\s*=\s*1\s*;\s*\$attempt\s*<=\s*self::MAX_NUMBER_ATTEMPTS/',
            $source,
            'generate() doit itérer jusqu\'à MAX_NUMBER_ATTEMPTS.'
        );
    }

    /**
     * Test 2 — DB-LEVEL : la contrainte UNIQUE sur `invoices.number` est
     * bien active et refuse deux invoices avec même numéro.
     */
    public function test_database_rejects_duplicate_invoice_number(): void
    {
        $root = $this->makeRootInstance();

        $sharedNumber = 'DUP-TEST-'.uniqid();

        Invoice::create([
            'instance_id' => $root->id,
            'number' => $sharedNumber,
            'amount' => 10,
            'tax' => 0,
            'total' => 10,
            'currency' => 'XOF',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->expectException(QueryException::class);

        Invoice::create([
            'instance_id' => $root->id,
            'number' => $sharedNumber,
            'amount' => 20,
            'tax' => 0,
            'total' => 20,
            'currency' => 'XOF',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);
    }

    /**
     * Test 3 — COMPORTEMENT : `generate()` produit des numéros séquentiels
     * distincts sur appels successifs. Vérifie que le flow nominal fonctionne
     * (la boucle de retry ne se déclenche qu'en cas de collision).
     */
    public function test_generate_produces_sequential_distinct_numbers(): void
    {
        $root = $this->makeRootInstance();

        $plan = Plan::create([
            'name' => 'Starter R-202',
            'slug' => 'starter-r202-'.uniqid(),
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'trial_days' => 0,
            'is_active' => true,
        ]);

        $sub = Subscription::create([
            'instance_id' => $root->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $manager = app(InvoiceManager::class);

        $invoice1 = $manager->generate($sub, 'monthly');
        $invoice2 = $manager->generate($sub, 'monthly');

        $number1 = (string) Invoice::query()->where('id', $invoice1->id)->value('number');
        $number2 = (string) Invoice::query()->where('id', $invoice2->id)->value('number');

        $this->assertNotEmpty($number1);
        $this->assertNotEmpty($number2);
        $this->assertNotSame($number1, $number2, 'Deux factures consécutives doivent avoir des numéros distincts.');
    }
}
