<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Models\SmsGateway;
use Modules\Eshop360\Models\SmsLog;
use Modules\Core\Support\CurrentInstance;

/**
 * SMS sending service for Eshop360.
 * Supports multiple configurable SMS gateways per instance.
 */
final class SmsService
{
    /**
     * Send an SMS message.
     */
    public function send(string $to, string $message, ?int $gatewayId = null): bool
    {
        $instance = CurrentInstance::get();
        if (!$instance) return false;

        $gateway = $gatewayId
            ? SmsGateway::find($gatewayId)
            : SmsGateway::where('instance_id', $instance->id)->where('is_default', true)->first();

        if (!$gateway) {
            Log::warning('No SMS gateway configured for instance ' . $instance->id);
            return false;
        }

        $result = $this->dispatch($gateway, $to, $message);

        // Log the SMS
        SmsLog::create([
            'instance_id' => $instance->id,
            'gateway_id' => $gateway->id,
            'to' => $to,
            'message' => $message,
            'status' => $result ? 'sent' : 'failed',
            'response' => $result['response'] ?? null,
        ]);

        return $result['success'] ?? false;
    }

    /**
     * Send SMS to multiple recipients.
     */
    public function sendBulk(array $recipients, string $message, ?int $gatewayId = null): array
    {
        $results = [];
        foreach ($recipients as $to) {
            $results[$to] = $this->send($to, $message, $gatewayId);
        }
        return $results;
    }

    /**
     * Dispatch SMS through the configured gateway provider.
     */
    private function dispatch(SmsGateway $gateway, string $to, string $message): array
    {
        $provider = $gateway->provider ?? 'generic';

        try {
            return match ($provider) {
                'twilio' => $this->sendViaTwilio($gateway, $to, $message),
                'infobip' => $this->sendViaInfobip($gateway, $to, $message),
                'orange_sms' => $this->sendViaOrangeSms($gateway, $to, $message),
                default => $this->sendViaGenericApi($gateway, $to, $message),
            };
        } catch (\Throwable $e) {
            Log::error('SMS dispatch failed', [
                'provider' => $provider,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'response' => $e->getMessage()];
        }
    }

    private function sendViaGenericApi(SmsGateway $gateway, string $to, string $message): array
    {
        $config = $gateway->config ?? [];
        $url = $config['api_url'] ?? '';
        $apiKey = $config['api_key'] ?? '';

        if (!$url) {
            return ['success' => false, 'response' => 'No API URL configured'];
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->post($url, [
            'to' => $to,
            'message' => $message,
            'from' => $config['sender_id'] ?? '',
        ]);

        return [
            'success' => $response->successful(),
            'response' => $response->body(),
        ];
    }

    private function sendViaTwilio(SmsGateway $gateway, string $to, string $message): array
    {
        $config = $gateway->config ?? [];
        $sid = $config['account_sid'] ?? '';
        $token = $config['auth_token'] ?? '';
        $from = $config['from_number'] ?? '';

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $to,
                'From' => $from,
                'Body' => $message,
            ]);

        return [
            'success' => $response->successful(),
            'response' => $response->body(),
        ];
    }

    private function sendViaInfobip(SmsGateway $gateway, string $to, string $message): array
    {
        $config = $gateway->config ?? [];
        $baseUrl = $config['base_url'] ?? '';
        $apiKey = $config['api_key'] ?? '';

        $response = Http::withHeaders([
            'Authorization' => "App {$apiKey}",
        ])->post("{$baseUrl}/sms/2/text/advanced", [
            'messages' => [[
                'from' => $config['sender_id'] ?? 'Eshop360',
                'destinations' => [['to' => $to]],
                'text' => $message,
            ]],
        ]);

        return [
            'success' => $response->successful(),
            'response' => $response->body(),
        ];
    }

    private function sendViaOrangeSms(SmsGateway $gateway, string $to, string $message): array
    {
        $config = $gateway->config ?? [];
        $authUrl = $config['auth_url'] ?? 'https://api.orange.com/oauth/v3/token';
        $smsUrl = $config['sms_url'] ?? '';
        $clientId = $config['client_id'] ?? '';
        $clientSecret = $config['client_secret'] ?? '';

        // Get OAuth token
        $authResponse = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post($authUrl, ['grant_type' => 'client_credentials']);

        if (!$authResponse->successful()) {
            return ['success' => false, 'response' => 'Auth failed: ' . $authResponse->body()];
        }

        $token = $authResponse->json('access_token');

        // Send SMS
        $response = Http::withToken($token)->post($smsUrl, [
            'outboundSMSMessageRequest' => [
                'address' => "tel:{$to}",
                'senderAddress' => "tel:{$config['sender_number']}",
                'outboundSMSTextMessage' => ['message' => $message],
            ],
        ]);

        return [
            'success' => $response->successful(),
            'response' => $response->body(),
        ];
    }
}
