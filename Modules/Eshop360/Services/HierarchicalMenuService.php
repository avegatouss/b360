<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Collection;
use Modules\Core\Hooks\HookManager;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Core\Support\CurrentInstance;

final class HierarchicalMenuService
{
    public function __construct(
        private readonly EshopSettingsService $settings,
    ) {}

    /**
     * Menu sections mapped to hierarchical "module" groups.
     * key = parent menu id prefix, value = [label, icon, color, features (required channel features, empty = always visible)]
     */
    private const MODULE_GROUPS = [
        'vente' => [
            'label' => 'Vente',
            'icon' => 'ti ti-shopping-cart',
            'color' => '#4f46e5',
            'sections' => ['eshop360.eshop', 'eshop360.ventes'],
            'features' => ['sales'],
        ],
        'achat' => [
            'label' => 'Achat',
            'icon' => 'ti ti-truck-delivery',
            'color' => '#0891b2',
            'sections' => ['eshop360.achats', 'eshop360.fournisseurs'],
            'features' => ['orders'],
        ],
        'produits' => [
            'label' => 'Produits',
            'icon' => 'ti ti-package',
            'color' => '#059669',
            'sections' => ['eshop360.catalogue'],
            'features' => [],
        ],
        'stocks' => [
            'label' => 'Stocks',
            'icon' => 'ti ti-building-warehouse',
            'color' => '#d97706',
            'sections' => ['eshop360.stocks'],
            'features' => ['stock'],
        ],
        'clients' => [
            'label' => 'Clients',
            'icon' => 'ti ti-users-group',
            'color' => '#dc2626',
            'sections' => ['eshop360.clients'],
            'features' => ['customers'],
        ],
        'factures' => [
            'label' => 'Factures',
            'icon' => 'ti ti-file-invoice',
            'color' => '#7c3aed',
            'sections' => ['eshop360.factures'],
            'features' => [],
        ],
        'finances' => [
            'label' => 'Finances',
            'icon' => 'ti ti-wallet',
            'color' => '#0d9488',
            'sections' => ['eshop360.finances'],
            'features' => ['finance'],
        ],
        'promotions' => [
            'label' => 'Promotions',
            'icon' => 'ti ti-discount',
            'color' => '#e11d48',
            'sections' => ['eshop360.promotions'],
            'features' => ['promotions'],
        ],
        'rh' => [
            'label' => 'Ressources Humaines',
            'icon' => 'ti ti-user-check',
            'color' => '#6366f1',
            'sections' => ['eshop360.rh'],
            'features' => ['hr'],
        ],
        'communication' => [
            'label' => 'Communication',
            'icon' => 'ti ti-message-circle',
            'color' => '#2563eb',
            'sections' => ['eshop360.communication'],
            'features' => ['support'],
        ],
        'rapports' => [
            'label' => 'Rapports',
            'icon' => 'ti ti-chart-bar',
            'color' => '#84cc16',
            'sections' => ['eshop360.rapports'],
            'features' => ['reports'],
        ],
        'portails' => [
            'label' => 'Portails & Canaux',
            'icon' => 'ti ti-building-store',
            'color' => '#f59e0b',
            'sections' => ['eshop360.portails'],
            'features' => ['portal'],
        ],
        'projets' => [
            'label' => 'Projets',
            'icon' => 'ti ti-checklist',
            'color' => '#8b5cf6',
            'sections' => ['eshop360.projects'],
            'features' => [],
        ],
        'charges' => [
            'label' => 'Charges',
            'icon' => 'ti ti-clock-dollar',
            'color' => '#64748b',
            'sections' => ['eshop360.charges'],
            'features' => ['finance'],
        ],
        'parametres' => [
            'label' => 'Paramètres',
            'icon' => 'ti ti-settings',
            'color' => '#64748b',
            'sections' => ['eshop360.channel_settings'],
            'features' => ['settings'],
        ],
    ];

    /**
     * Check if a channel is the hub (central) channel.
     */
    public function isHubChannel(DistributionChannel $channel): bool
    {
        return (bool) $channel->is_hub;
    }

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
            ->orderByDesc('is_hub')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if a slug corresponds to the hub channel.
     */
    public function isGlobalChannel(string $slug): bool
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            return false;
        }

        return DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('slug', $slug)
            ->where('is_hub', true)
            ->exists();
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
     * When a channel is provided, filters groups by the channel's enabled features.
     * The global channel (Saphir Plus) sees all modules.
     */
    public function getModuleGroups(?DistributionChannel $channel = null): array
    {
        $menuItems = $this->getSidebarMenu();

        $groups = [];
        foreach (self::MODULE_GROUPS as $key => $def) {
            // Filter by channel features (global channel sees everything)
            if ($channel && !$this->isHubChannel($channel) && !empty($def['features'])) {
                $hasFeature = false;
                $featureDefaults = $this->settings->defaults('features');
                foreach ($def['features'] as $feature) {
                    $default = $featureDefaults[$feature] ?? false;
                    if ($this->settings->isChannelFeatureEnabled($feature, $channel->id, $default)) {
                        $hasFeature = true;
                        break;
                    }
                }
                if (!$hasFeature) {
                    continue;
                }
            }

            $children = collect();

            foreach ($def['sections'] as $sectionId) {
                $parent = $menuItems->first(fn (MenuItem $item) => $item->id === $sectionId);
                if ($parent) {
                    if ($parent->hasChildren()) {
                        $children = $children->merge($parent->children);
                    } elseif ($parent->route) {
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
    public function getModuleChildren(string $moduleKey, ?DistributionChannel $channel = null): ?array
    {
        $groups = $this->getModuleGroups($channel);
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
