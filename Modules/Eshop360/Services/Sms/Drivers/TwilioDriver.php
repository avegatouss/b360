<?php

namespace Modules\Eshop360\Services\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Services\Sms\SmsDriverInterface;

class TwilioDriver implements SmsDriverInterface
{
    public function __construct(protected array $config) {}

    public function send(string $to, string $message): bool
    {
        $sid = $this->config['account_sid'] ?? '';
        $token = $this->config['auth_token'] ?? '';
        $from = $this->config['from_number'] ?? '';

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Twilio SMS failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Twilio SMS exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function getBalance(): ?float
    {
        $sid = $this->config['account_sid'] ?? '';
        $token = $this->config['auth_token'] ?? '';

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->get("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Balance.json");

            if ($response->successful()) {
                return (float) $response->json('balance');
            }
        } catch (\Throwable $e) {
            Log::error('Twilio balance check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'account_sid', 'label' => 'Account SID', 'type' => 'text', 'required' => true],
            ['name' => 'auth_token', 'label' => 'Auth Token', 'type' => 'password', 'required' => true],
            ['name' => 'from_number', 'label' => 'From Number', 'type' => 'text', 'required' => true],
        ];
    }
}
