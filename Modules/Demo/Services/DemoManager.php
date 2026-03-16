<?php

namespace Modules\Demo\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Hooks\DTO\DemoDataProvider;
use Modules\Core\Hooks\Registry\HookRegistry;

/**
 * Orchestrates demo data installation and reset.
 *
 * Modules register DemoDataProvider hooks, each pointing to a seeder class.
 * DemoManager can run all seeders, a specific one, or reset to minimum data.
 */
final class DemoManager
{
    public function __construct(
        private readonly HookRegistry $hookRegistry,
    ) {}

    /**
     * Get all registered demo data providers.
     */
    public function providers(): Collection
    {
        return $this->hookRegistry->demoProviders();
    }

    /**
     * Get providers grouped by module.
     */
    public function byModule(): Collection
    {
        return $this->providers()->groupBy('module');
    }

    /**
     * Get providers grouped by category.
     */
    public function byCategory(): Collection
    {
        return $this->providers()->groupBy('category');
    }

    /**
     * Install demo data for a specific provider.
     */
    public function seed(string $providerId, int $instanceId): array
    {
        $provider = $this->providers()->firstWhere('id', $providerId);

        if (!$provider) {
            return ['success' => false, 'message' => "Provider '{$providerId}' introuvable."];
        }

        return $this->runSeeder($provider, $instanceId);
    }

    /**
     * Install all demo data for a specific module.
     */
    public function seedModule(string $module, int $instanceId): array
    {
        $providers = $this->providers()->where('module', $module);
        $results = [];

        foreach ($providers as $provider) {
            $results[$provider->id] = $this->runSeeder($provider, $instanceId);
        }

        return $results;
    }

    /**
     * Install all demo data (all modules, all providers).
     */
    public function seedAll(int $instanceId): array
    {
        $results = [];

        foreach ($this->providers() as $provider) {
            $results[$provider->id] = $this->runSeeder($provider, $instanceId);
        }

        return $results;
    }

    /**
     * Reset demo data for a provider — truncates then re-seeds with minimum base data.
     */
    public function reset(string $providerId, int $instanceId): array
    {
        $provider = $this->providers()->firstWhere('id', $providerId);

        if (!$provider) {
            return ['success' => false, 'message' => "Provider '{$providerId}' introuvable."];
        }

        $seederClass = $provider->seederClass;

        if (!class_exists($seederClass)) {
            return ['success' => false, 'message' => "Seeder class '{$seederClass}' introuvable."];
        }

        $seeder = app($seederClass);

        // If the seeder has a reset() method, call it
        if (method_exists($seeder, 'reset')) {
            try {
                $seeder->reset($instanceId);
                return ['success' => true, 'message' => "Donnees '{$provider->label}' reinitialisees."];
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => "Le seeder ne supporte pas la reinitialisation."];
    }

    /**
     * Reset all demo data for all providers.
     */
    public function resetAll(int $instanceId): array
    {
        $results = [];

        foreach ($this->providers() as $provider) {
            $results[$provider->id] = $this->reset($provider->id, $instanceId);
        }

        return $results;
    }

    private function runSeeder(DemoDataProvider $provider, int $instanceId): array
    {
        $seederClass = $provider->seederClass;

        if (!class_exists($seederClass)) {
            return ['success' => false, 'message' => "Seeder class '{$seederClass}' introuvable."];
        }

        try {
            $seeder = app($seederClass);
            $seeder->run($instanceId);

            return ['success' => true, 'message' => "'{$provider->label}' installe avec succes."];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
