<?php

namespace Modules\Core\Hooks\DTO;

final class BillableFeature
{
    /**
     * @param string      $id          Unique feature identifier (e.g. 'eshop360.channels')
     * @param string      $label       Human-readable label
     * @param string      $module      Module name (e.g. 'Eshop360')
     * @param string      $tier        'free' or 'paid'
     * @param int         $priority    Sort priority (higher = first)
     * @param string|null $description Long description for upgrade pages
     * @param string|null $category    Grouping category (e.g. 'catalog', 'finance')
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $module,
        public readonly string $tier = 'free',
        public readonly int $priority = 0,
        public readonly ?string $description = null,
        public readonly ?string $category = null,
    ) {}
}
