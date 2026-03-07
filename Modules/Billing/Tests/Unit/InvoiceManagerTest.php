<?php

namespace Modules\Billing\Tests\Unit;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\InvoiceManager;
use Modules\Billing\Tests\TestCase;

final class InvoiceManagerTest extends TestCase
{
    private InvoiceManager $manager;
    private Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(InvoiceManager::class);

        $plan = Plan::create([
            'name' => 'Test',
            'slug' => 'test',
            'price_monthly' => 29.99,
            'trial_days' => 0,
        ]);

        $this->subscription = Subscription::create([
            'instance_id' => 1,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);
    }

    public function test_generate_invoice(): void
    {
        $invoice = $this->manager->generate($this->subscription);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame(1, $invoice->instance_id);
        $this->assertSame($this->subscription->id, $invoice->subscription_id);
        $this->assertEquals(29.99, $invoice->amount);
        $this->assertSame('pending', $invoice->status);
    }

    public function test_next_number_format(): void
    {
        $number = $this->manager->nextNumber();
        $year = now()->year;

        $this->assertMatchesRegularExpression("/^B360-INV-{$year}-\\d{5}$/", $number);
    }

    public function test_next_number_increments(): void
    {
        $first = $this->manager->generate($this->subscription);
        $second = $this->manager->generate($this->subscription);

        $this->assertNotSame($first->number, $second->number);

        $firstSeq = (int) substr($first->number, strrpos($first->number, '-') + 1);
        $secondSeq = (int) substr($second->number, strrpos($second->number, '-') + 1);
        $this->assertSame($firstSeq + 1, $secondSeq);
    }

    public function test_mark_paid_creates_payment(): void
    {
        $invoice = $this->manager->generate($this->subscription);

        $payment = $this->manager->markPaid($invoice, 'card', 'REF-123');

        $this->assertSame('completed', $payment->status);
        $this->assertSame('card', $payment->method);
        $this->assertSame('REF-123', $payment->reference);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_mark_paid_sets_paid_at(): void
    {
        $invoice = $this->manager->generate($this->subscription);

        $this->manager->markPaid($invoice, 'manual');

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_mark_failed(): void
    {
        $invoice = $this->manager->generate($this->subscription);

        $failed = $this->manager->markFailed($invoice);

        $this->assertSame('failed', $failed->status);
    }

    public function test_for_instance(): void
    {
        $this->manager->generate($this->subscription);
        $this->manager->generate($this->subscription);

        $invoices = $this->manager->forInstance(1);
        $this->assertCount(2, $invoices);

        $empty = $this->manager->forInstance(999);
        $this->assertCount(0, $empty);
    }

    public function test_overdue_returns_unpaid_past_due(): void
    {
        // Create an overdue invoice
        Invoice::create([
            'instance_id' => 1,
            'number' => 'B360-INV-2025-00001',
            'amount' => 10,
            'tax' => 0,
            'total' => 10,
            'status' => 'pending',
            'due_date' => now()->subDays(5),
        ]);

        // Create a non-overdue invoice
        Invoice::create([
            'instance_id' => 1,
            'number' => 'B360-INV-2025-00002',
            'amount' => 10,
            'tax' => 0,
            'total' => 10,
            'status' => 'pending',
            'due_date' => now()->addDays(10),
        ]);

        $overdue = $this->manager->overdue();
        $this->assertCount(1, $overdue);
        $this->assertSame('B360-INV-2025-00001', $overdue->first()->number);
    }
}
