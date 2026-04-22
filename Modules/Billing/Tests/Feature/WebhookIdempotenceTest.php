<?php

namespace Modules\Billing\Tests\Feature;

use Illuminate\Database\UniqueConstraintViolationException;
use Mockery;
use Modules\Billing\Contracts\WebhookResult;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\WebhookLog;
use Modules\Billing\Services\GatewayManager;
use Modules\Billing\Tests\TestCase;

/**
 * R-002 — Idempotence des webhooks Billing.
 *
 * Vérifie que :
 *   1. un webhook déjà reçu (même idempotency_key) n'est pas re-traité
 *   2. les webhooks sans identifiant exploitable retombent sur un hash
 *      du corps brut pour garantir la déduplication
 *   3. deux webhooks distincts produisent deux logs
 *   4. un webhook malformé est journalisé mais renvoie 400
 *   5. la contrainte UNIQUE au niveau DB ferme la race window même si
 *      le check applicatif la laisse passer
 */
final class WebhookIdempotenceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makePayment(string $reference = 'PAY-REF-001'): Payment
    {
        $root = $this->makeRootInstance();

        $plan = Plan::create([
            'name' => 'Starter',
            'slug' => 'starter-'.uniqid(),
            'price_monthly' => 10,
            'trial_days' => 0,
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'instance_id' => $root->id,
            'number' => 'INV-'.uniqid(),
            'amount' => 10,
            'tax' => 0,
            'total' => 10,
            'currency' => 'XOF',
            'status' => 'pending',
            'due_date' => now()->addDays(15),
        ]);

        return Payment::create([
            'invoice_id' => $invoice->id,
            'reference' => $reference,
            'amount' => 10,
            'currency' => 'XOF',
            'method' => 'stripe',
            'status' => 'pending',
            'gateway_slug' => 'stripe',
        ]);
    }

    private function mockGatewayWebhookResult(bool $valid, string $status, ?string $internalRef = null, ?string $gatewayRef = null): void
    {
        $result = new WebhookResult(
            valid: $valid,
            gatewayReference: $gatewayRef,
            internalReference: $internalRef,
            status: $status,
        );

        $mock = Mockery::mock(GatewayManager::class);
        $mock->shouldReceive('handleWebhook')
            ->andReturn($result);

        $this->app->instance(GatewayManager::class, $mock);
    }

    public function test_webhook_replay_does_not_reprocess_payment(): void
    {
        $payment = $this->makePayment('PAY-REF-001');
        $this->mockGatewayWebhookResult(valid: true, status: 'completed', internalRef: 'PAY-REF-001');

        $payload = ['id' => 'evt_123', 'type' => 'payment_intent.succeeded'];

        // 1er envoi : traité normalement
        $first = $this->postJson('/api/billing/webhooks/stripe', $payload);
        $first->assertOk();
        $first->assertSeeText('OK');

        $status = Payment::query()->where('id', $payment->id)->value('status');
        $firstPaidAt = Payment::query()->where('id', $payment->id)->value('paid_at');
        $this->assertSame('completed', $status);
        $this->assertNotNull($firstPaidAt);

        $this->assertSame(1, WebhookLog::count(), 'Un seul log après le 1er webhook.');

        // 2e envoi IDENTIQUE : doit être détecté comme replay
        sleep(1); // garantit que toute mise à jour produirait un paid_at différent
        $second = $this->postJson('/api/billing/webhooks/stripe', $payload);
        $second->assertOk();
        $second->assertSeeText('replay');

        $secondPaidAt = Payment::query()->where('id', $payment->id)->value('paid_at');
        $this->assertEquals(
            (string) $firstPaidAt,
            (string) $secondPaidAt,
            'paid_at ne doit pas être réécrit par un replay.'
        );
        $this->assertSame(1, WebhookLog::count(), 'Aucun nouveau log sur replay.');
    }

    public function test_webhook_without_idempotency_data_uses_payload_hash(): void
    {
        $this->mockGatewayWebhookResult(valid: true, status: 'unknown');

        // Payload sans id/event_id/transaction_id → fallback hash du corps
        $payload = ['type' => 'ping', 'data' => ['nonce' => 'abc-123']];

        $this->postJson('/api/billing/webhooks/manual', $payload)->assertOk();
        $this->assertSame(1, WebhookLog::count());

        // Même payload → même hash → replay détecté
        $this->postJson('/api/billing/webhooks/manual', $payload)->assertOk();
        $this->assertSame(
            1,
            WebhookLog::count(),
            'Fallback hash du corps doit dédupliquer aussi.'
        );

        $idempotencyKey = WebhookLog::query()->value('idempotency_key');
        $this->assertNotNull($idempotencyKey);
        $this->assertStringContainsString('manual:', (string) $idempotencyKey);
    }

    public function test_two_different_webhooks_create_two_logs(): void
    {
        $this->mockGatewayWebhookResult(valid: true, status: 'completed');

        $this->postJson('/api/billing/webhooks/stripe', ['id' => 'evt_001'])->assertOk();
        $this->postJson('/api/billing/webhooks/stripe', ['id' => 'evt_002'])->assertOk();

        $this->assertSame(2, WebhookLog::count(), 'Deux événements distincts = deux logs.');
    }

    public function test_malformed_webhook_is_logged_but_returns_400(): void
    {
        $this->mockGatewayWebhookResult(valid: false, status: 'unknown');

        $response = $this->postJson('/api/billing/webhooks/stripe', ['id' => 'evt_bad']);
        $response->assertStatus(400);

        $this->assertSame(1, WebhookLog::count(), 'Le webhook invalide reste tracé pour audit.');

        $log = WebhookLog::first();
        $this->assertSame('evt_bad', $log->payload['id'] ?? null);
    }

    public function test_database_enforces_unique_idempotency_key(): void
    {
        // Garantie qu'au niveau DB, deux logs avec la même clé ne peuvent
        // pas coexister — ferme la race window même si le check applicatif
        // était court-circuité.
        WebhookLog::create([
            'gateway_slug' => 'stripe',
            'idempotency_key' => 'stripe:evt_unique',
            'payload' => ['id' => 'evt_unique'],
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        WebhookLog::create([
            'gateway_slug' => 'stripe',
            'idempotency_key' => 'stripe:evt_unique',
            'payload' => ['id' => 'evt_unique', 'retry' => true],
        ]);
    }
}
