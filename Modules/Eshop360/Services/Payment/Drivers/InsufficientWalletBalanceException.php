<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use RuntimeException;

/**
 * Levée par WalletDriver::initiate() quand le solde du portefeuille
 * est insuffisant au moment de la vérification SOUS LOCK.
 *
 * Signale un flux utilisateur légitime (client sans crédit suffisant),
 * pas un bug. Le caller (WalletDriver) la traduit en `['success' => false,
 * 'error' => …]` métier, le caller supérieur (PublicPaymentController)
 * affiche un message clair à l'utilisateur.
 */
final class InsufficientWalletBalanceException extends RuntimeException
{
    public function __construct(
        public readonly float $available,
        public readonly float $requested,
    ) {
        parent::__construct("Solde insuffisant ({$available} < {$requested})");
    }
}
