<?php

namespace Modules\Billing\Tests\Unit;

use Modules\Billing\Contracts\PaymentRequest;
use Modules\Billing\Gateways\ManualGateway;
use PHPUnit\Framework\TestCase;

final class ManualGatewayTest extends TestCase
{
    private ManualGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new ManualGateway();
        $this->gateway->configure([
            'instructions' => 'Envoyez par virement.',
            'bank_name' => 'BanqueTest',
            'account_number' => '123456',
            'iban' => 'CI93000000123456',
        ]);
    }

    public function test_id(): void
    {
        $this->assertEquals('manual', $this->gateway->id());
    }

    public function test_always_configured(): void
    {
        $this->assertTrue($this->gateway->isConfigured());
    }

    public function test_test_connection_always_succeeds(): void
    {
        $result = $this->gateway->testConnection();
        $this->assertTrue($result['success']);
    }

    public function test_initiate_returns_pending(): void
    {
        $request = new PaymentRequest(
            invoiceId: 1,
            amount: 5000,
            currency: 'XOF',
            description: 'Test',
            reference: 'REF-001',
            callbackUrl: 'https://example.com/callback',
            returnUrl: 'https://example.com/return',
        );

        $response = $this->gateway->initiate($request);

        $this->assertTrue($response->success);
        $this->assertEquals('pending', $response->status);
        $this->assertStringContains('MANUAL-', $response->gatewayReference);
        $this->assertNotNull($response->metadata['instructions']);
        $this->assertEquals('BanqueTest', $response->metadata['bank_name']);
    }

    public function test_no_webhooks(): void
    {
        $result = $this->gateway->verifyWebhook([]);
        $this->assertFalse($result->valid);
    }

    public function test_no_refunds(): void
    {
        $this->assertFalse($this->gateway->supportsRefunds());

        $result = $this->gateway->refund('ref', 1000, 'XOF');
        $this->assertFalse($result->success);
    }

    public function test_supported_currencies(): void
    {
        $currencies = $this->gateway->supportedCurrencies();
        $this->assertContains('XOF', $currencies);
        $this->assertContains('EUR', $currencies);
    }

    private function assertStringContains(string $needle, ?string $haystack): void
    {
        $this->assertNotNull($haystack);
        $this->assertStringContainsString($needle, $haystack);
    }
}
