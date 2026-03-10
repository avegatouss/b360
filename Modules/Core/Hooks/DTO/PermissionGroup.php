<?php

namespace Modules\Core\Hooks\DTO;

final class PermissionGroup
{
    /**
     * @param string $id       Unique group identifier (e.g. 'users', 'billing')
     * @param string $label    Human-readable label (e.g. 'Utilisateurs', 'Facturation')
     * @param array  $permissions  List of permissions: ['permission.name' => 'Label']
     * @param int    $priority Sort priority (higher = first)
     * @param string|null $module Module name (for display grouping)
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly array $permissions,
        public readonly int $priority = 0,
        public readonly ?string $module = null,
    ) {}
}
