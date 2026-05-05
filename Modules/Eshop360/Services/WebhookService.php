<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Webhook;
use Modules\Eshop360\Models\WebhookLog;

class WebhookService
{
    /**
     * Supported webhook events.
     */
    public const EVENTS = [
        'order.created',
        'order.completed',
        'order.cancelled',
        'payment.received',
        'invoice.created',
        'invoice.paid',
        'stock.low',
        'stock.adjusted',
        'customer.created',
        'product.created',
        'product.updated',
    ];

    /**
     * Dispatch a webhook event to all subscribed endpoints for the current instance.
     */
    public function dispatch(string $event, array $payload = []): void
    {
        $instanceId = CurrentInstance::idOrFail();

        // Deduplication: build a unique key from event + entity to prevent duplicate dispatches
        $deduplicationKey = $this->buildDeduplicationKey($event, $payload);
        if ($deduplicationKey && WebhookLog::where('deduplication_key', $deduplicationKey)->exists()) {
            Log::debug('Webhook dispatch skipped (duplicate)', ['event' => $event, 'key' => $deduplicationKey]);

            return;
        }

        $webhooks = Webhook::where('instance_id', $instanceId)
            ->where('is_active', true)
            ->where('failure_count', '<', 10) // Auto-disable after 10 consecutive failures
            ->get()
            ->filter(fn (Webhook $wh) => $wh->subscribedTo($event));

        foreach ($webhooks as $webhook) {
            // Dispatch async via queue to avoid blocking
            dispatch(function () use ($webhook, $event, $payload, $deduplicationKey) {
                $this->send($webhook, $event, $payload, $deduplicationKey);
            })->onQueue('webhooks')->afterCommit();
        }
    }

    /**
     * Build a deduplication key from event type and entity identifiers.
     */
    private function buildDeduplicationKey(string $event, array $payload): ?string
    {
        $entityId = $payload['id'] ?? $payload['order_id'] ?? $payload['invoice_id'] ?? null;
        if ($entityId === null) {
            return null;
        }

        return hash('sha256', "{$event}:{$entityId}");
    }

    /**
     * Send a webhook payload to a single endpoint.
     */
    public function send(Webhook $webhook, string $event, array $payload, ?string $deduplicationKey = null): void
    {
        $body = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'instance_id' => $webhook->instance_id,
            'data' => $payload,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $event,
            'X-Webhook-Id' => (string) $webhook->id,
        ];

        // Sign payload with HMAC if secret is set
        if ($webhook->secret) {
            $headers['X-Webhook-Signature'] = hash_hmac('sha256', json_encode($body), $webhook->secret);
        }

        $startTime = microtime(true);
        $responseCode = null;
        $responseBody = null;
        $success = false;

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->connectTimeout(5)
                ->post($webhook->url, $body);

            $responseCode = $response->status();
            $responseBody = mb_substr($response->body(), 0, 2000);
            $success = $response->successful();
        } catch (\Throwable $e) {
            $responseBody = mb_substr($e->getMessage(), 0, 2000);
            Log::warning('Webhook delivery failed', [
                'webhook_id' => $webhook->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        // Log the attempt with deduplication key for future duplicate detection
        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'response_code' => $responseCode,
            'duration_ms' => $durationMs,
            'payload' => json_encode($body),
            'response_body' => $responseBody,
            'success' => $success,
            'deduplication_key' => $deduplicationKey,
            'created_at' => now(),
        ]);

        // Update webhook status
        if ($success) {
            $webhook->update([
                'failure_count' => 0,
                'last_triggered_at' => now(),
            ]);
        } else {
            $webhook->increment('failure_count');
            $webhook->update(['last_failed_at' => now()]);
        }
    }

    /**
     * Test a webhook endpoint with a ping event.
     */
    public function ping(Webhook $webhook): array
    {
        $body = [
            'event' => 'ping',
            'timestamp' => now()->toIso8601String(),
            'instance_id' => $webhook->instance_id,
            'data' => ['message' => 'Webhook test from B360'],
        ];

        $headers = ['Content-Type' => 'application/json', 'X-Webhook-Event' => 'ping'];
        if ($webhook->secret) {
            $headers['X-Webhook-Signature'] = hash_hmac('sha256', json_encode($body), $webhook->secret);
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->post($webhook->url, $body);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $response->successful() ? 'Webhook accessible.' : 'HTTP '.$response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => null,
                'message' => $e->getMessage(),
            ];
        }
    }
}
