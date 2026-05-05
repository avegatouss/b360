<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Communication\Models\Webhook;
use Modules\Eshop360\Domain\Communication\Models\WebhookLog;

final class DemoWebhooksSeeder
{
    public function run(int $instanceId): void
    {
        $webhooks = [
            [
                'name' => '[DEMO] ERP Notification',
                'url' => 'https://webhook.site/demo-erp-endpoint',
                'secret' => 'demo-secret-erp-2026',
                'events' => ['order.created', 'order.completed', 'payment.received'],
                'is_active' => true,
                'failure_count' => 0,
            ],
            [
                'name' => '[DEMO] Stock Alertes Slack',
                'url' => 'https://hooks.slack.com/services/DEMO/WEBHOOK/placeholder',
                'secret' => null,
                'events' => ['stock.low', 'stock.adjusted'],
                'is_active' => true,
                'failure_count' => 0,
            ],
            [
                'name' => '[DEMO] CRM Sync (desactive)',
                'url' => 'https://api.demo-crm.com/webhooks/b360',
                'secret' => 'crm-hmac-key-demo',
                'events' => ['customer.created', 'invoice.created', 'invoice.paid'],
                'is_active' => false,
                'failure_count' => 3,
                'last_failed_at' => now()->subDays(2),
            ],
        ];

        foreach ($webhooks as $data) {
            $webhook = Webhook::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $data['name']],
                array_merge($data, ['instance_id' => $instanceId])
            );

            // Add sample logs for active webhooks
            if ($webhook->is_active && $webhook->logs()->count() === 0) {
                $this->seedSampleLogs($webhook);
            }
        }
    }

    public function reset(int $instanceId): void
    {
        $webhookIds = Webhook::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->pluck('id');

        WebhookLog::whereIn('webhook_id', $webhookIds)->delete();

        Webhook::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')
            ->delete();
    }

    private function seedSampleLogs(Webhook $webhook): void
    {
        $events = $webhook->events;
        $now = now();

        foreach (array_slice($events, 0, 2) as $i => $event) {
            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'response_code' => 200,
                'duration_ms' => rand(80, 350),
                'payload' => json_encode([
                    'event' => $event,
                    'timestamp' => $now->subHours($i + 1)->toIso8601String(),
                    'instance_id' => $webhook->instance_id,
                    'data' => ['demo' => true],
                ]),
                'response_body' => '{"status":"ok"}',
                'success' => true,
                'created_at' => $now->subHours($i + 1),
            ]);
        }

        // One failed log
        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => $events[0] ?? 'order.created',
            'response_code' => 500,
            'duration_ms' => rand(1000, 3000),
            'payload' => json_encode(['event' => $events[0] ?? 'order.created', 'data' => ['demo' => true]]),
            'response_body' => 'Internal Server Error',
            'success' => false,
            'created_at' => $now->subDays(1),
        ]);
    }
}
