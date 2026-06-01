<?php

namespace Modules\Eshop360\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\CRM\Models\CustomerTransaction;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\Payment\Drivers\WalletDriver;
use Modules\Eshop360\Tests\TestCase;

/**
 * R-003 — Intégrité du solde portefeuille.
 *
 * Ces tests couvrent les 4 chantiers du lot :
 *   1. Contrainte CHECK `wallet_balance >= 0` (SGBD, MySQL-only)
 *   2. WalletDriver::initiate() : check sous lock (plus de TOCTOU)
 *   3. WalletDriver::refund() : increment sous lock
 *   4. Les appelants historiques (ChannelPortal topup, SaleReturn wallet
 *      refund) passent désormais par FinanceService → CustomerTransaction
 *      d'audit créée et auto-pay des dues appliquée.
 *
 * Voir ADR-004-wallet-integrity-strategy.
 */
final class WalletIntegrityTest extends TestCase
{
    private function makeCustomer(int $instanceId, float $balance = 0.0): Customer
    {
        return Customer::create([
            'instance_id' => $instanceId,
            'code' => 'CUS-WAL-'.uniqid(),
            'name' => 'Wallet Customer',
            'wallet_balance' => $balance,
            'credit_limit' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Lit wallet_balance via le query builder (PHPStan-friendly :
     * évite l'accès propriété dynamique sur Customer).
     */
    private function walletBalance(int $customerId): string
    {
        return (string) Customer::query()->where('id', $customerId)->value('wallet_balance');
    }

    /**
     * Test 1 — STRUCTURAL : migration CHECK présente et correcte.
     * Verrouille l'architecture : toute suppression ou modification
     * de la contrainte cassera ce test.
     */
    public function test_check_constraint_migration_exists_with_correct_sql(): void
    {
        $migration = base_path(
            'Modules/Eshop360/Database/Migrations/2026_04_22_110001_add_check_constraint_wallet_balance.php'
        );

        $this->assertFileExists($migration, 'Migration CHECK wallet_balance présente.');

        $source = (string) file_get_contents($migration);

        $this->assertStringContainsString(
            'CHECK (wallet_balance >= 0)',
            $source,
            'La migration doit poser le CHECK wallet_balance >= 0.'
        );
        $this->assertStringContainsString(
            'chk_wallet_balance_non_negative',
            $source,
            'Nom de contrainte stable pour retrouver/supprimer.'
        );
        $this->assertStringContainsString(
            "'sqlite'",
            $source,
            'Détection SQLite pour no-op en tests unitaires.'
        );
    }

    /**
     * Test 2 — Contrainte DB active sur MySQL, skippée sur SQLite.
     */
    public function test_database_enforces_non_negative_wallet_on_mysql(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql') {
            $this->markTestSkipped(
                "CHECK wallet_balance non vérifiable sur driver [{$driver}] — "
                .'voir migration 2026_04_22_110001 et ADR-004.'
            );
        }

        $constraint = DB::selectOne(
            'SELECT CONSTRAINT_NAME, CHECK_CLAUSE
             FROM information_schema.CHECK_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ?
               AND CONSTRAINT_NAME = ?',
            [DB::connection()->getDatabaseName(), 'chk_wallet_balance_non_negative']
        );

        $this->assertNotNull(
            $constraint,
            'La contrainte chk_wallet_balance_non_negative doit exister en MySQL.'
        );

        $this->assertMatchesRegularExpression(
            '/wallet_balance\s*>=\s*0/',
            (string) ($constraint->CHECK_CLAUSE ?? ''),
            'La contrainte doit imposer wallet_balance >= 0.'
        );
    }

    /**
     * Test 3 — STRUCTURAL : WalletDriver::initiate() fait le check
     * À L'INTÉRIEUR de la transaction, sous lockForUpdate.
     * Verrouille l'ancien pattern TOCTOU (check hors transaction).
     */
    public function test_wallet_driver_source_uses_lock_for_balance_check(): void
    {
        $source = (string) file_get_contents(
            base_path('Modules/Eshop360/Services/Payment/Drivers/WalletDriver.php')
        );

        // Le check `wallet_balance < amount` doit être exécuté à l'intérieur
        // d'un DB::transaction ET précédé d'un lockForUpdate().
        $this->assertStringContainsString(
            'DB::transaction(function () use',
            $source,
            'initiate() doit muter le solde dans DB::transaction.'
        );
        $this->assertStringContainsString(
            'lockForUpdate()',
            $source,
            'initiate() doit verrouiller la ligne Customer avant le check.'
        );
        $this->assertStringContainsString(
            'InsufficientWalletBalanceException',
            $source,
            'initiate() doit lever une exception typée sur solde insuffisant.'
        );

        // L'ancien pattern `$customer->wallet_balance < $amount` hors
        // transaction doit avoir disparu.
        $this->assertDoesNotMatchRegularExpression(
            '/\$customer->wallet_balance\s*<\s*\$amount[^;]*;\s*[\r\n]+\s*return\s*\[\s*\'success\'\s*=>\s*false/s',
            $source,
            'Le check hors transaction (TOCTOU) ne doit plus exister.'
        );
    }

    /**
     * Test 4 — COMPORTEMENT : deux débits séquentiels sur un solde
     * de 100 voient le second refusé (pattern race simulée).
     * Le lock garantit que le deuxième ne peut pas descendre sous zéro.
     */
    public function test_wallet_driver_sequential_debits_never_produce_negative(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();
        $customer = $this->makeCustomer($instance->id, 100.0);

        $driver = new WalletDriver;

        // 1er débit : 80 OK
        $first = $driver->initiate(80.0, 'XOF', ['customer_id' => $customer->id]);
        $this->assertTrue($first['success'], 'Le 1er débit doit passer.');

        $this->assertSame('20.00', $this->walletBalance($customer->id));

        // 2e débit : 50 doit être refusé (solde = 20, pas 100)
        $second = $driver->initiate(50.0, 'XOF', ['customer_id' => $customer->id]);
        $this->assertFalse($second['success'], 'Le 2e débit doit être refusé.');
        $this->assertStringContainsString('Solde insuffisant', (string) $second['error']);

        // Solde final garanti >= 0
        $finalBalance = $this->walletBalance($customer->id);
        $this->assertSame('20.00', $finalBalance);
        $this->assertGreaterThanOrEqual(0, (float) $finalBalance);
    }

    /**
     * Test 5 — Le refactor SaleController::storeReturn avec
     * refund_method=wallet passe par FinanceService : une
     * CustomerTransaction d'audit doit être créée (prouvant que
     * l'ancien `increment()` direct non-tracé a bien été remplacé).
     *
     * Test simplifié : on appelle directement creditWallet avec le
     * même pattern que le controller, et on vérifie l'audit trail.
     */
    public function test_finance_service_credit_wallet_creates_audit_transaction(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();
        $customer = $this->makeCustomer($instance->id, 0.0);

        $service = app(FinanceService::class);

        // Scénario : refund de 500 sur un retour de vente #42
        $service->creditWallet(
            $customer,
            500.0,
            'Remboursement retour vente #42',
            'Modules\\Eshop360\\Models\\Order',
            42,
        );

        $this->assertSame('500.00', $this->walletBalance($customer->id));

        $transaction = CustomerTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', 'credit')
            ->first();

        $this->assertNotNull($transaction, 'Un CustomerTransaction d\'audit doit exister.');

        $this->assertSame(
            '500.00',
            (string) CustomerTransaction::query()->where('id', $transaction->id)->value('amount')
        );
        $this->assertStringContainsString(
            'vente #42',
            (string) CustomerTransaction::query()->where('id', $transaction->id)->value('notes')
        );
        $this->assertSame(
            'Modules\\Eshop360\\Models\\Order',
            (string) CustomerTransaction::query()->where('id', $transaction->id)->value('reference_type')
        );
        $this->assertSame(
            42,
            (int) CustomerTransaction::query()->where('id', $transaction->id)->value('reference_id')
        );
    }

    /**
     * Test 6 — STRUCTURAL : ChannelPortalCustomerController::walletTopup
     * passe désormais par FinanceService (plus de increment direct).
     */
    public function test_channel_portal_topup_source_uses_finance_service(): void
    {
        $source = (string) file_get_contents(
            base_path('Modules/Eshop360/Http/Controllers/ChannelPortal/ChannelPortalCustomerController.php')
        );

        $this->assertStringContainsString(
            'use Modules\\Eshop360\\Services\\FinanceService;',
            $source,
            'Le controller doit importer FinanceService.'
        );
        $this->assertStringContainsString(
            '$financeService->creditWallet(',
            $source,
            'walletTopup doit appeler FinanceService::creditWallet.'
        );

        // L'ancien pattern `$customer->increment('wallet_balance', ...)`
        // doit avoir disparu.
        $this->assertDoesNotMatchRegularExpression(
            '/\$customer->increment\s*\(\s*[\'"]wallet_balance[\'"]/',
            $source,
            'Plus aucun increment direct sur wallet_balance.'
        );
    }

    /**
     * Test 7 — STRUCTURAL : SaleController::storeReturn refund=wallet
     * passe par FinanceService.
     */
    public function test_sale_return_source_uses_finance_service_for_wallet_refund(): void
    {
        $source = (string) file_get_contents(
            base_path('Modules/Eshop360/Http/Controllers/Sales/SaleController.php')
        );

        $this->assertStringContainsString(
            'use Modules\\Eshop360\\Services\\FinanceService;',
            $source,
            'SaleController doit importer FinanceService.'
        );
        $this->assertStringContainsString(
            'creditWallet(',
            $source,
            'Le refund wallet doit appeler FinanceService::creditWallet.'
        );

        // L'ancien pattern `Customer::where(...)->increment('wallet_balance', ...)`
        // doit avoir disparu.
        $this->assertDoesNotMatchRegularExpression(
            '/Customer::where\s*\([^)]*\)\s*->\s*increment\s*\(\s*[\'"]wallet_balance[\'"]/',
            $source,
            'Plus aucun Customer::where(...)->increment(wallet_balance).'
        );
    }
}
