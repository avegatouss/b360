<?php

namespace Modules\Eshop360\Services\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Services\Sms\SmsDriverInterface;

class ClockworkDriver implements SmsDriverInterface
{
    public function __construct(protected array $config) {}

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::get('https://api.clockworksms.com/http/send.aspx', [
                'key' => $this->config['api_key'] ?? '',
                'to' => $to,
                'content' => $message,
            ]);

            if ($response->successful()) {
                $body = $response->body();
                // Clockwork returns "To: ...\nID: ..." on success, "Error ..." on failure
                if (str_contains($body, 'ID:')) {
                    return true;
                }
                Log::error('Clockwork SMS rejected', ['response' => $body]);
                return false;
            }

            Log::error('Clockwork SMS failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Clockwork SMS exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getBalance(): ?float
    {
        try {
            $response = Http::get('https://api.clockworksms.com/http/balance', [
                'key' => $this->config['api_key'] ?? '',
            ]);

            if ($response->successful()) {
                // Response format: "Balance: 10.50"
                $body = $response->body();
                if (preg_match('/Balance:\s*([\d.]+)/', $body, $matches)) {
                    return (float) $matches[1];
                }
            }
        } catch (\Throwable $e) {
            Log::error('Clockwork balance check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'required' => true],
        ];
    }
}
