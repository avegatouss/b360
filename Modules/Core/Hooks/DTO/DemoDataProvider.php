<?php

namespace Modules\Core\Hooks\DTO;

final class DemoDataProvider
{
    /**
     * @param string   $id          Unique identifier (e.g. 'eshop360.catalog')
     * @param string   $label       Human-readable label (e.g. 'Catalogue pharmaceutique')
     * @param string   $module      Contributing module (e.g. 'Eshop360')
     * @param string   $seederClass FQCN of the seeder to run
     * @param int      $priority    Sort priority (higher = first)
     * @param string|null $description Description of what data is seeded
     * @param string|null $category   Grouping (e.g. 'catalog', 'finance', 'hr')
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $module,
        public readonly string $seederClass,
        public readonly int $priority = 0,
        public readonly ?string $description = null,
        public readonly ?string $category = null,
    ) {}
}
