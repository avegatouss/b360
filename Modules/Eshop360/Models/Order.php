<?php

namespace Modules\Eshop360\Models;

/**
 * Backward-compatibility alias.
 *
 * Canonical location: Modules\Eshop360\Domain\Sales\Models\Order
 *
 * R-101 S8 : extraction du sous-domaine Sales (L1 critique).
 *
 * Note: the canonical class pins `$morphClass` to this legacy FQN so
 * stored `reference_type` / `payable_type` values remain stable.
 */
class Order extends \Modules\Eshop360\Domain\Sales\Models\Order {}
