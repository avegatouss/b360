<?php

namespace Modules\Billing\Tests\Unit;

use Modules\Billing\Gateways\ManualGateway;
use Modules\Billing\Services\GatewayManager;
use Modules\Billing\Tests\TestCase;
use Modules\Core\Hooks\DTO\PaymentGatewayDefinition;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Settings\Services\SettingsManager;

final class GatewayManagerTest extends TestCase
{
    private HookRegistry $hookRegistry;
    private GatewayManager $gatewayManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hookRegistry = new HookRegistry();
        $settingsManager = app(SettingsManager::class);

        $this->gatewayManager = new GatewayManager($this->hookRegistry, $settingsManager);
    }

    public function test_resolve_manual_gateway(): void
    {
        $this->hookRegistry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'manual',
            label: 'Manual',
            module: 'Billing',
            driverClass: ManualGateway::class,
        ));

        $root = $this->makeRootInstance();
        $driver = $this->gatewayManager->resolve('manual', $root->id);

        $this->assertInstanceOf(ManualGateway::class, $driver);
        $this->assertTrue($driver->isConfigured());
    }

    public function test_resolve_unknown_gateway_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->gatewayManager->resolve('nonexistent', 1);
    }

    public function test_all_returns_registered_gateways(): void
    {
        $this->hookRegistry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'gw1',
            label: 'GW1',
            module: 'Billing',
            driverClass: ManualGateway::class,
        ));

        $this->assertCount(1, $this->gatewayManager->all());
    }

    public function test_toggle_enables_and_disables(): void
    {
        $this->hookRegistry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'manual',
            label: 'Manual',
            module: 'Billing',
            driverClass: ManualGateway::class,
        ));

        $root = $this->makeRootInstance();

        $this->assertFalse($this->gatewayManager->isEnabled('manual', $root->id));

        $this->gatewayManager->toggle('manual', $root->id, true);
        $this->assertTrue($this->gatewayManager->isEnabled('manual', $root->id));

        $this->gatewayManager->toggle('manual', $root->id, false);
        $this->assertFalse($this->gatewayManager->isEnabled('manual', $root->id));
    }

    public function test_enabled_for_returns_only_enabled(): void
    {
        $this->hookRegistry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'gw_on',
            label: 'ON',
            module: 'Billing',
            driverClass: ManualGateway::class,
        ));

        $this->hookRegistry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'gw_off',
            label: 'OFF',
            module: 'Billing',
            driverClass: ManualGateway::class,
        ));

        $root = $this->makeRootInstance();
        $this->gatewayManager->toggle('gw_on', $root->id, true);

        $enabled = $this->gatewayManager->enabledFor($root->id);

        $this->assertCount(1, $enabled);
        $this->assertEquals('gw_on', $enabled->first()->id);
    }

    public function test_save_and_read_credentials(): void
    {
        $root = $this->makeRootInstance();

        $this->gatewayManager->saveCredentials('stripe', $root->id, [
            'secret_key' => 'sk_test_123',
            'publishable_key' => 'pk_test_123',
        ]);

        $creds = $this->gatewayManager->credentials('stripe', $root->id);

        $this->assertEquals('sk_test_123', $creds['secret_key']);
        $this->assertEquals('pk_test_123', $creds['publishable_key']);
    }

    public function test_test_connection_manual(): void
    {
        $this->hookRegistry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'manual',
            label: 'Manual',
            module: 'Billing',
            driverClass: ManualGateway::class,
        ));

        $root = $this->makeRootInstance();
        $result = $this->gatewayManager->testConnection('manual', $root->id);

        $this->assertTrue($result['success']);
    }
}
