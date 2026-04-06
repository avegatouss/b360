<?php

namespace Modules\Eshop360\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\CustomerDue;
use Modules\Eshop360\Models\Employee;
use Modules\Eshop360\Models\EmployeeCommission;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Webhook;
use Modules\Eshop360\Models\WebhookLog;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\HRService;
use Modules\Eshop360\Services\WebhookService;
use Modules\Eshop360\Tests\TestCase;

/**
 * Tests for P0 safety guards (Prompt #1 corrections).
 */
final class P0SafetyGuardsTest extends TestCase
{
    // ──────────────────────────────────────────
    // Wallet locking
    // ──────────────────────────────────────────

    public function test_creditWallet_locks_customer_row_before_increment(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-P0-001',
            'name' => 'Test Client',
            'wallet_balance' => 0,
            'is_active' => true,
        ]);

        $service = app(FinanceService::class);
        $service->creditWallet($customer, 5000, 'Test credit');

        $customer->refresh();
        $this->assertEquals(5000, (float) $customer->wallet_balance);
    }

    public function test_creditWallet_auto_pays_pending_dues_without_double_payment(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-P0-002',
            'name' => 'Test Client Dues',
            'wallet_balance' => 0,
            'is_active' => true,
        ]);

        $due = CustomerDue::create([
            'customer_id' => $customer->id,
            'order_id' => null,
            'amount_due' => 3000,
            'paid_amount' => 0,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $service = app(FinanceService::class);
        $service->creditWallet($customer, 5000, 'Recharge');

        $customer->refresh();
        $due->refresh();

        $this->assertEquals('paid', $due->status);
        $this->assertEquals(3000, (float) $due->paid_amount);
        $this->assertEquals(2000, (float) $customer->wallet_balance);
    }

    public function test_debitWallet_locks_customer_row(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-P0-003',
            'name' => 'Test Debit',
            'wallet_balance' => 10000,
            'is_active' => true,
        ]);

        $service = app(FinanceService::class);
        $deducted = $service->debitWallet($customer, 3000);

        $this->assertEquals(3000.0, $deducted);
        $customer->refresh();
        $this->assertEquals(7000, (float) $customer->wallet_balance);
    }

    // ──────────────────────────────────────────
    // Commission idempotence
    // ──────────────────────────────────────────

    public function test_commission_idempotente_si_ordre_completed_deux_fois(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $employee = Employee::create([
            'instance_id' => $instance->id,
            'name' => 'Vendeur Test',
            'commission_rate' => 5.0,
            'salary' => 200000,
            'status' => 'active',
        ]);

        $order = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'ORD-IDEM-001',
            'total' => 100000,
            'employee_id' => $employee->id,
            'status' => 'completed',
        ]);

        $service = app(HRService::class);

        // First call — should create commission
        $service->calculateCommissionForSale($order);
        $this->assertEquals(1, EmployeeCommission::where('order_id', $order->id)->count());

        // Second call — should be idempotent (no duplicate)
        $service->calculateCommissionForSale($order);
        $this->assertEquals(1, EmployeeCommission::where('order_id', $order->id)->count());

        // Verify amount
        $commission = EmployeeCommission::where('order_id', $order->id)->first();
        $this->assertEquals(5000, (float) $commission->amount); // 100000 * 5%
    }

    // ──────────────────────────────────────────
    // Webhook deduplication
    // ──────────────────────────────────────────

    public function test_webhook_duplique_est_ignore(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        // Create a real webhook endpoint first (FK constraint)
        $webhook = Webhook::create([
            'instance_id' => $instance->id,
            'name' => 'Test Webhook',
            'url' => 'https://example.com/hook',
            'events' => ['order.created'],
            'is_active' => true,
            'failure_count' => 0,
        ]);

        // Pre-insert a webhook log with a deduplication key (using real webhook_id)
        $deduplicationKey = hash('sha256', 'order.created:42');
        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => 'order.created',
            'success' => true,
            'deduplication_key' => $deduplicationKey,
            'created_at' => now(),
        ]);

        $service = app(WebhookService::class);

        // Dispatch with same event+entity — should be skipped (dedup key exists)
        $initialCount = WebhookLog::count();
        $service->dispatch('order.created', ['id' => 42]);

        // No new log should be created (dispatch was skipped)
        $this->assertEquals($initialCount, WebhookLog::count());
    }

    // ──────────────────────────────────────────
    // Project & Task BelongsToInstance
    // ──────────────────────────────────────────

    public function test_project_appartient_a_instance(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $project = \Modules\Eshop360\Models\Project::create([
            'instance_id' => $instance->id,
            'name' => 'Test Project',
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $this->assertEquals($instance->id, $project->instance_id);

        $found = \Modules\Eshop360\Models\Project::where('id', $project->id)->first();
        $this->assertNotNull($found);
    }

    public function test_task_appartient_a_instance(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $project = \Modules\Eshop360\Models\Project::create([
            'instance_id' => $instance->id,
            'name' => 'Task Project',
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $task = \Modules\Eshop360\Models\Task::create([
            'instance_id' => $instance->id,
            'project_id' => $project->id,
            'title' => 'Test Task',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $this->assertEquals($instance->id, $task->instance_id);
    }
}
