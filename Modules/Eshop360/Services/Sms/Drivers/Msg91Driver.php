<?php

namespace Modules\Eshop360\Services\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Services\Sms\SmsDriverInterface;

class Msg91Driver implements SmsDriverInterface
{
    public function __construct(protected array $config) {}

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'authkey' => $this->config['auth_key'] ?? '',
                'Content-Type' => 'application/json',
            ])->post('https://api.msg91.com/api/v5/flow/', [
                'template_id' => $this->config['template_id'] ?? '',
                'sender' => $this->config['sender_id'] ?? '',
                'short_url' => '0',
                'mobiles' => $to,
                'VAR1' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['type'] ?? '') === 'success') {
                    return true;
                }
                Log::error('MSG91 SMS rejected', ['response' => $data]);

                return false;
            }

            Log::error('MSG91 SMS failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('MSG91 SMS exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function getBalance(): ?float
    {
        try {
            $response = Http::withHeaders([
                'authkey' => $this->config['auth_key'] ?? '',
            ])->get('https://api.msg91.com/api/balance.php', [
                'authkey' => $this->config['auth_key'] ?? '',
                'type' => 1, // promotional
            ]);

            if ($response->successful()) {
                return (float) $response->body();
            }
        } catch (\Throwable $e) {
            Log::error('MSG91 balance check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'auth_key', 'label' => 'Auth Key', 'type' => 'password', 'required' => true],
            ['name' => 'sender_id', 'label' => 'Sender ID', 'type' => 'text', 'required' => true],
            ['name' => 'template_id', 'label' => 'Template ID', 'type' => 'text', 'required' => true],
        ];
    }
}
