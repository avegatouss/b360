<?php

namespace Modules\Eshop360\Models;

/**
 * Backward-compatibility alias.
 *
 * Canonical location: Modules\Eshop360\Domain\Finance\Models\EshopPaymentGateway
 *
 * R-101 S9 : extraction du sous-domaine Finance (L1 critique).
 * The canonical pins $morphClass to this legacy FQN so stored morph
 * _type values remain stable across the extraction.
 */
class EshopPaymentGateway extends \Modules\Eshop360\Domain\Finance\Models\EshopPaymentGateway {}
