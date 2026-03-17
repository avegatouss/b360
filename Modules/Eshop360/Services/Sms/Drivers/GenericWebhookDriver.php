<?php

namespace Modules\Eshop360\Services\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Services\Sms\SmsDriverInterface;

class GenericWebhookDriver implements SmsDriverInterface
{
    public function __construct(protected array $config) {}

    public function send(string $to, string $message): bool
    {
        $url = $this->config['url'] ?? '';
        $method = strtoupper($this->config['method'] ?? 'POST');
        $paramTo = $this->config['param_to'] ?? 'to';
        $paramMessage = $this->config['param_message'] ?? 'message';

        if (empty($url)) {
            Log::error('GenericWebhook SMS: no URL configured');
            return false;
        }

        // Parse headers JSON
        $headers = [];
        $headersRaw = $this->config['headers'] ?? '';
        if (!empty($headersRaw)) {
            if (is_string($headersRaw)) {
                $headers = json_decode($headersRaw, true) ?? [];
            } elseif (is_array($headersRaw)) {
                $headers = $headersRaw;
            }
        }

        // Parse extra params JSON
        $extraParams = [];
        $extraRaw = $this->config['extra_params'] ?? '';
        if (!empty($extraRaw)) {
            if (is_string($extraRaw)) {
                $extraParams = json_decode($extraRaw, true) ?? [];
            } elseif (is_array($extraRaw)) {
                $extraParams = $extraRaw;
            }
        }

        $params = array_merge($extraParams, [
            $paramTo => $to,
            $paramMessage => $message,
        ]);

        try {
            $request = Http::withHeaders($headers);

            $response = match ($method) {
                'GET' => $request->get($url, $params),
                default => $request->post($url, $params),
            };

            if ($response->successful()) {
                return true;
            }

            Log::error('GenericWebhook SMS failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('GenericWebhook SMS exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getBalance(): ?float
    {
        // Generic webhook does not support balance checking
        return null;
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'url', 'label' => 'Webhook URL', 'type' => 'text', 'required' => true],
            ['name' => 'method', 'label' => 'HTTP Method', 'type' => 'select', 'required' => true, 'options' => ['GET', 'POST']],
            ['name' => 'param_to', 'label' => 'Phone Parameter Name', 'type' => 'text', 'required' => true, 'default' => 'to'],
            ['name' => 'param_message', 'label' => 'Message Parameter Name', 'type' => 'text', 'required' => true, 'default' => 'message'],
            ['name' => 'headers', 'label' => 'Custom Headers (JSON)', 'type' => 'textarea', 'required' => false],
            ['name' => 'extra_params', 'label' => 'Extra Parameters (JSON)', 'type' => 'textarea', 'required' => false],
        ];
    }
}
