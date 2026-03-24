<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Collection;
use Modules\Core\Hooks\HookManager;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Core\Support\CurrentInstance;

final class HierarchicalMenuService
{
    /**
     * Menu sections mapped to hierarchical "module" groups.
     * key = parent menu id prefix, value = [label, icon, color]
     */
    private const MODULE_GROUPS = [
        'vente' => [
            'label' => 'Vente',
            'icon' => 'ti ti-shopping-cart',
            'color' => '#4f46e5',
            'sections' => ['eshop360.eshop', 'eshop360.ventes'],
        ],
        'achat' => [
            'label' => 'Achat',
            'icon' => 'ti ti-truck-delivery',
            'color' => '#0891b2',
            'sections' => ['eshop360.achats', 'eshop360.fournisseurs'],
        ],
        'produits' => [
            'label' => 'Produits',
            'icon' => 'ti ti-package',
            'color' => '#059669',
            'sections' => ['eshop360.catalogue'],
        ],
        'stocks' => [
            'label' => 'Stocks',
            'icon' => 'ti ti-building-warehouse',
            'color' => '#d97706',
            'sections' => ['eshop360.stocks'],
        ],
        'clients' => [
            'label' => 'Clients',
            'icon' => 'ti ti-users-group',
            'color' => '#dc2626',
            'sections' => ['eshop360.clients'],
        ],
        'factures' => [
            'label' => 'Factures',
            'icon' => 'ti ti-file-invoice',
            'color' => '#7c3aed',
            'sections' => ['eshop360.factures'],
        ],
        'finances' => [
            'label' => 'Finances',
            'icon' => 'ti ti-wallet',
            'color' => '#0d9488',
            'sections' => ['eshop360.finances'],
        ],
        'promotions' => [
            'label' => 'Promotions',
            'icon' => 'ti ti-discount',
            'color' => '#e11d48',
            'sections' => ['eshop360.promotions'],
        ],
        'rh' => [
            'label' => 'Ressources Humaines',
            'icon' => 'ti ti-user-check',
            'color' => '#6366f1',
            'sections' => ['eshop360.rh'],
        ],
        'communication' => [
            'label' => 'Communication',
            'icon' => 'ti ti-message-circle',
            'color' => '#2563eb',
            'sections' => ['eshop360.communication'],
        ],
        'rapports' => [
            'label' => 'Rapports',
            'icon' => 'ti ti-chart-bar',
            'color' => '#84cc16',
            'sections' => ['eshop360.rapports'],
        ],
        'portails' => [
            'label' => 'Portails & Canaux',
            'icon' => 'ti ti-building-store',
            'color' => '#f59e0b',
            'sections' => ['eshop360.portails'],
        ],
        'projets' => [
            'label' => 'Projets',
            'icon' => 'ti ti-checklist',
            'color' => '#8b5cf6',
            'sections' => ['eshop360.projects'],
        ],
        'charges' => [
            'label' => 'Charges',
            'icon' => 'ti ti-clock-dollar',
            'color' => '#64748b',
            'sections' => ['eshop360.charges'],
        ],
    ];

    /**
     * Slug of the global channel that gives access to all menu items.
     */
    public const GLOBAL_CHANNEL_SLUG = 'saphir-plus';

    /**
     * Get the active distribution channels for the current instance.
     */
    public function getChannels(): Collection
    {
        $instance = CurrentInstance::get();

        if (!$instance) {
            return collect();
        }

        return DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN slug = ? THEN 0 ELSE 1 END", [self::GLOBAL_CHANNEL_SLUG])
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if a slug corresponds to the global channel.
     */
    public function isGlobalChannel(string $slug): bool
    {
        return $slug === self::GLOBAL_CHANNEL_SLUG;
    }

    /**
     * Find a channel by slug.
     */
    public function findChannel(string $slug): ?DistributionChannel
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            return null;
        }

        return DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get module groups with their aggregated children from the sidebar menu.
     * Each module group merges children from all its associated sidebar sections.
     */
    public function getModuleGroups(): array
    {
        $menuItems = $this->getSidebarMenu();

        $groups = [];
        foreach (self::MODULE_GROUPS as $key => $def) {
            $children = collect();

            foreach ($def['sections'] as $sectionId) {
                $parent = $menuItems->first(fn (MenuItem $item) => $item->id === $sectionId);
                if ($parent) {
                    if ($parent->hasChildren()) {
                        $children = $children->merge($parent->children);
                    } elseif ($parent->route) {
                        // Single item (no children), add itself
                        $children->push($parent);
                    }
                }
            }

            if ($children->isEmpty()) {
                continue;
            }

            $groups[$key] = [
                'key' => $key,
                'label' => $def['label'],
                'icon' => $def['icon'],
                'color' => $def['color'],
                'children' => $children->values()->all(),
                'count' => $children->count(),
            ];
        }

        return $groups;
    }

    /**
     * Get a specific module group's children.
     */
    public function getModuleChildren(string $moduleKey): ?array
    {
        $groups = $this->getModuleGroups();
        return $groups[$moduleKey] ?? null;
    }

    /**
     * Get the full sidebar menu from HookRegistry.
     */
    private function getSidebarMenu(): Collection
    {
        return app(HookManager::class)->registry()->menu();
    }
}
