<?php

namespace Modules\Core\Hooks\DTO;

final class PaymentGatewayDefinition
{
    /**
     * @param string      $id                  Unique gateway slug (e.g. 'stripe', 'cinetpay')
     * @param string      $label               Human-readable name (e.g. 'CinetPay')
     * @param string      $module              Contributing module (e.g. 'Billing')
     * @param string      $driverClass         FQCN implementing PaymentGatewayInterface
     * @param int         $priority            Sort priority (higher = first)
     * @param string|null $icon                Icon class (e.g. 'ti ti-credit-card')
     * @param string|null $settingsView        Blade view for credentials form
     * @param array       $supportedCurrencies e.g. ['XOF', 'XAF', 'EUR']
     * @param array       $supportedRegions    e.g. ['CI', 'SN', 'CM']
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $module,
        public readonly string $driverClass,
        public readonly int $priority = 0,
        public readonly ?string $icon = null,
        public readonly ?string $settingsView = null,
        public readonly array $supportedCurrencies = [],
        public readonly array $supportedRegions = [],
    ) {}
}
