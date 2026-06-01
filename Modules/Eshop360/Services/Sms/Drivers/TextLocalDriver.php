<?php

namespace Modules\Eshop360\Services\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Services\Sms\SmsDriverInterface;

class TextLocalDriver implements SmsDriverInterface
{
    public function __construct(protected array $config) {}

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::asForm()->post('https://api.textlocal.in/send/', [
                'apikey' => $this->config['api_key'] ?? '',
                'sender' => $this->config['sender'] ?? '',
                'numbers' => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    return true;
                }
                Log::error('TextLocal SMS rejected', [
                    'errors' => $data['errors'] ?? $data,
                ]);

                return false;
            }

            Log::error('TextLocal SMS failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('TextLocal SMS exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function getBalance(): ?float
    {
        try {
            $response = Http::asForm()->post('https://api.textlocal.in/balance/', [
                'apikey' => $this->config['api_key'] ?? '',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return (float) ($data['balance']['sms'] ?? 0);
            }
        } catch (\Throwable $e) {
            Log::error('TextLocal balance check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'required' => true],
            ['name' => 'sender', 'label' => 'Sender Name', 'type' => 'text', 'required' => true],
        ];
    }
}
