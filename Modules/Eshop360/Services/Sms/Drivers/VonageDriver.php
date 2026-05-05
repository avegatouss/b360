<?php

namespace Modules\Eshop360\Services\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Services\Sms\SmsDriverInterface;

class VonageDriver implements SmsDriverInterface
{
    public function __construct(protected array $config) {}

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::post('https://rest.nexmo.com/sms/json', [
                'api_key' => $this->config['api_key'] ?? '',
                'api_secret' => $this->config['api_secret'] ?? '',
                'from' => $this->config['from'] ?? '',
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['messages'][0]['status'] ?? '1';
                if ($status === '0') {
                    return true;
                }
                Log::error('Vonage SMS rejected', [
                    'status' => $status,
                    'error' => $data['messages'][0]['error-text'] ?? 'Unknown error',
                ]);

                return false;
            }

            Log::error('Vonage SMS failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Vonage SMS exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function getBalance(): ?float
    {
        try {
            $response = Http::get('https://rest.nexmo.com/account/get-balance', [
                'api_key' => $this->config['api_key'] ?? '',
                'api_secret' => $this->config['api_secret'] ?? '',
            ]);

            if ($response->successful()) {
                return (float) $response->json('value');
            }
        } catch (\Throwable $e) {
            Log::error('Vonage balance check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'required' => true],
            ['name' => 'api_secret', 'label' => 'API Secret', 'type' => 'password', 'required' => true],
            ['name' => 'from', 'label' => 'From (Sender Name/Number)', 'type' => 'text', 'required' => true],
        ];
    }
}
