<?php

namespace Modules\Eshop360\Http\Controllers\Traits;

use Modules\Eshop360\Services\CashRegisterService;
use Modules\Eshop360\Services\EshopSettingsService;

trait ResolvesPosContext
{
    /**
     * Resolve POS operational data (store, warehouse, cash register) from settings and current register.
     *
     * @return array{store_id?: int, warehouse_id?: int, cash_register_id?: int}
     */
    protected function resolvePosOperationalData(): array
    {
        $settings = app(EshopSettingsService::class)->get('pos');
        $register = app(CashRegisterService::class)->getCurrentRegister();
        $warehouseId = $settings['default_warehouse_id'] ?? $register?->store?->warehouse_id ?? null;

        return array_filter([
            'store_id' => $register?->store_id,
            'warehouse_id' => $warehouseId,
            'cash_register_id' => $register?->id,
        ], static fn ($value) => $value !== null);
    }
}
