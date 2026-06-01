<?php

namespace Modules\Billing\Providers;

use Modules\Billing\Gateways\CinetPayGateway;
use Modules\Billing\Gateways\InetPayGateway;
use Modules\Billing\Gateways\ManualGateway;
use Modules\Billing\Gateways\MtnMomoGateway;
use Modules\Billing\Gateways\OrangeMoneyGateway;
use Modules\Billing\Gateways\PayPalGateway;
use Modules\Billing\Gateways\StripeGateway;
use Modules\Billing\Gateways\WaveGateway;
use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PaymentGatewayDefinition;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\DTO\SettingsGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\TeamContext;

final class BillingHooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Billing';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $this->registerMenuItems($registry);
        $this->registerSettings($registry);
        $this->registerPermissions($registry);
        $this->registerPaymentGateways($registry);
    }

    private function registerMenuItems(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(
            id: 'billing',
            label: 'Facturation',
            route: 'billing.index',
            icon: 'ti ti-receipt',
            priority: 700,
            requiredPermission: 'billing.view',
            requiredModule: 'Billing',
            group: 'admin',
        ));

        $registry->addMenu(new MenuItem(
            id: 'billing-plans',
            label: 'Plans tarifaires',
            route: 'billing.plans.index',
            icon: 'ti ti-list-details',
            priority: 710,
            requiredPermission: 'billing.manage',
            requiredModule: 'Billing',
            group: 'admin',
            parentId: 'billing',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        $registry->addMenu(new MenuItem(
            id: 'billing-gateways',
            label: 'Passerelles de paiement',
            route: 'billing.gateways.index',
            icon: 'ti ti-credit-card',
            priority: 720,
            requiredPermission: 'billing.gateways.manage',
            requiredModule: 'Billing',
            group: 'admin',
            parentId: 'billing',
            visibleWhen: fn ($user, $instance) =>
                $instance?->isRoot() && TeamContext::isSuperAdmin($user),
        ));

        $registry->addMenu(new MenuItem(
            id: 'billing-invoices',
            label: 'Factures',
            route: 'billing.invoices.index',
            icon: 'ti ti-file-invoice',
            priority: 690,
            requiredPermission: 'billing.view',
            requiredModule: 'Billing',
            group: 'admin',
            parentId: 'billing',
        ));
    }

    private function registerSettings(HookRegistry $registry): void
    {
        $registry->addSettingsGroup(new SettingsGroup(
            id: 'billing',
            label: 'Facturation',
            view: 'billing::settings',
            priority: 600,
        ));
    }

    private function registerPermissions(HookRegistry $registry): void
    {
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'billing',
            label: 'Facturation & Abonnements',
            permissions: [
                'billing.view' => 'Voir les abonnements et factures',
                'billing.manage' => 'Gerer les plans et paiements',
                'billing.gateways.manage' => 'Configurer les passerelles de paiement',
                'billing.features.view' => 'Voir les fonctionnalites disponibles',
            ],
            priority: 700,
            module: 'Billing',
        ));
    }

    private function registerPaymentGateways(HookRegistry $registry): void
    {
        $registry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'manual',
            label: 'Virement / Paiement manuel',
            module: 'Billing',
            driverClass: ManualGateway::class,
            priority: 100,
            icon: 'ti ti-building-bank',
            settingsView: 'billing::gateways.partials.manual',
            supportedCurrencies: ['XOF', 'XAF', 'EUR', 'USD', 'GBP', 'GNF'],
        ));

        $registry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'cinetpay',
            label: 'CinetPay',
            module: 'Billing',
            driverClass: CinetPayGateway::class,
            priority: 90,
            icon: 'ti ti-wallet',
            settingsView: 'billing::gateways.partials.cinetpay',
            supportedCurrencies: ['XOF', 'XAF', 'GNF', 'USD'],
            supportedRegions: ['CI', 'SN', 'CM', 'BF', 'ML', 'TG', 'BJ', 'GN', 'CD', 'CG'],
        ));

        $registry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'orange_money',
            label: 'Orange Money',
            module: 'Billing',
            driverClass: OrangeMoneyGateway::class,
            priority: 85,
            icon: 'ti ti-device-mobile',
            settingsView: 'billing::gateways.partials.orange_money',
            supportedCurrencies: ['XOF'],
            supportedRegions: ['CI', 'SN', 'ML', 'BF', 'GN', 'CM'],
        ));

        $registry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'mtn_momo',
            label: 'MTN Mobile Money',
            module: 'Billing',
            driverClass: MtnMomoGateway::class,
            priority: 80,
            icon: 'ti ti-device-mobile',
            settingsView: 'billing::gateways.partials.mtn_momo',
            supportedCurrencies: ['XOF', 'XAF', 'EUR'],
            supportedRegions: ['CI', 'CM', 'BJ', 'CG', 'GH', 'UG'],
        ));

        $registry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'wave',
            label: 'Wave',
            module: 'Billing',
            driverClass: WaveGateway::class,
            priority: 75,
            icon: 'ti ti-wave-sine',
            settingsView: 'billing::gateways.partials.wave',
            supportedCurrencies: ['XOF'],
            supportedRegions: ['SN', 'CI'],
        ));

        $registry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'inetpay',
            label: 'InetPay',
            module: 'Billing',
            driverClass: InetPayGateway::class,
            priority: 70,
            icon: 'ti ti-cash',
            settingsView: 'billing::gateways.partials.inetpay',
            supportedCurrencies: ['XOF', 'XAF'],
        ));

        $registry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'stripe',
            label: 'Stripe',
            module: 'Billing',
            driverClass: StripeGateway::class,
            priority: 60,
            icon: 'ti ti-brand-stripe',
            settingsView: 'billing::gateways.partials.stripe',
            supportedCurrencies: ['EUR', 'USD', 'GBP', 'XOF', 'XAF', 'CAD', 'CHF', 'MAD'],
        ));

        $registry->addPaymentGateway(new PaymentGatewayDefinition(
            id: 'paypal',
            label: 'PayPal',
            module: 'Billing',
            driverClass: PayPalGateway::class,
            priority: 50,
            icon: 'ti ti-brand-paypal',
            settingsView: 'billing::gateways.partials.paypal',
            supportedCurrencies: ['EUR', 'USD', 'GBP', 'CAD', 'CHF'],
        ));
    }
}
