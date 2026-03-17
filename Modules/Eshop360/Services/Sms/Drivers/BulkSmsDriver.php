<?php

namespace Modules\Eshop360\Services\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Services\Sms\SmsDriverInterface;

class BulkSmsDriver implements SmsDriverInterface
{
    public function __construct(protected array $config) {}

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::withBasicAuth(
                $this->config['token_id'] ?? '',
                $this->config['token_secret'] ?? ''
            )->post('https://api.bulksms.com/v1/messages', [
                'to' => $to,
                'body' => $message,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('BulkSMS send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('BulkSMS exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getBalance(): ?float
    {
        try {
            $response = Http::withBasicAuth(
                $this->config['token_id'] ?? '',
                $this->config['token_secret'] ?? ''
            )->get('https://api.bulksms.com/v1/profile');

            if ($response->successful()) {
                return (float) ($response->json('credits.balance') ?? 0);
            }
        } catch (\Throwable $e) {
            Log::error('BulkSMS balance check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'token_id', 'label' => 'Token ID', 'type' => 'text', 'required' => true],
            ['name' => 'token_secret', 'label' => 'Token Secret', 'type' => 'password', 'required' => true],
        ];
    }
}
