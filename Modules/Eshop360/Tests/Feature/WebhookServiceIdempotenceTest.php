<?php

namespace Modules\Eshop360\Tests\Feature;

use Illuminate\Database\UniqueConstraintViolationException;
use Modules\Eshop360\Domain\Communication\Models\Webhook;
use Modules\Eshop360\Domain\Communication\Models\WebhookLog;
use Modules\Eshop360\Tests\TestCase;

/**
 * R-002 — Idempotence des webhooks Eshop360.
 *
 * Complète P0SafetyGuardsTest::test_webhook_duplique_est_ignore
 * (check applicatif) en validant le NOUVEAU filet DB : la contrainte
 * UNIQUE sur `eshop_webhook_logs.deduplication_key` empêche une race
 * window où deux `dispatch()` concurrents verraient tous deux
 * `exists() === false` avant leur insertion.
 *
 * Voir ADR-003-webhook-idempotency-strategy.
 */
final class WebhookServiceIdempotenceTest extends TestCase
{
    public function test_database_enforces_unique_deduplication_key(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $webhook = Webhook::create([
            'instance_id' => $instance->id,
            'name' => 'Test Webhook',
            'url' => 'https://example.com/hook',
            'events' => ['order.created'],
            'is_active' => true,
            'failure_count' => 0,
        ]);

        $dedupKey = hash('sha256', 'order.created:12345');

        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => 'order.created',
            'success' => true,
            'deduplication_key' => $dedupKey,
            'created_at' => now(),
        ]);

        // Une 2e insertion avec la même clé doit être refusée par la DB
        // (défense qui ferme la race window du check applicatif).
        $this->expectException(UniqueConstraintViolationException::class);

        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => 'order.created',
            'success' => true,
            'deduplication_key' => $dedupKey,
            'created_at' => now(),
        ]);
    }

    public function test_multiple_null_deduplication_keys_are_allowed(): void
    {
        // NULL != NULL sous UNIQUE : les anciens logs pré-R-002 restent
        // valides côte à côte sans conflit.
        [$instance] = $this->setUpInstanceWithAdmin();

        $webhook = Webhook::create([
            'instance_id' => $instance->id,
            'name' => 'Legacy Webhook',
            'url' => 'https://example.com/legacy',
            'events' => ['*'],
            'is_active' => true,
            'failure_count' => 0,
        ]);

        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => 'legacy.event',
            'success' => true,
            'deduplication_key' => null,
            'created_at' => now(),
        ]);

        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => 'legacy.event',
            'success' => true,
            'deduplication_key' => null,
            'created_at' => now(),
        ]);

        $this->assertSame(
            2,
            WebhookLog::where('webhook_id', $webhook->id)->count(),
            'Deux logs avec deduplication_key NULL doivent coexister.'
        );
    }
}
