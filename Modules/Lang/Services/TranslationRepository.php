<?php

namespace Modules\Lang\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Lang\Models\Translation;
use Modules\Core\Support\CurrentInstance;

class TranslationRepository
{
    /**
     * Get all translations for a locale and group.
     * Instance-specific translations override global (instance_id=0) ones.
     */
    public function get(string $locale, string $group): array
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;
        $cacheKey = "translations.{$instanceId}.{$locale}.{$group}";

        return Cache::remember($cacheKey, 3600, function () use ($locale, $group, $instanceId) {
            $translations = Translation::where('locale', $locale)
                ->where('group', $group)
                ->where(function ($q) use ($instanceId) {
                    $q->where('instance_id', 0);
                    if ($instanceId > 0) {
                        $q->orWhere('instance_id', $instanceId);
                    }
                })
                ->orderBy('instance_id', 'asc') // global first, instance overrides
                ->get()
                ->pluck('value', 'key')
                ->toArray();

            return $translations;
        });
    }

    /**
     * Set a translation value.
     */
    public function set(string $locale, string $group, string $key, string $value, int $instanceId = 0): Translation
    {
        $translation = Translation::updateOrCreate(
            [
                'instance_id' => $instanceId,
                'locale' => $locale,
                'group' => $group,
                'key' => $key,
            ],
            ['value' => $value]
        );

        $this->clearCache($locale, $group, $instanceId);
        return $translation;
    }

    /**
     * Bulk set translations.
     */
    public function bulkSet(string $locale, string $group, array $translations, int $instanceId = 0): void
    {
        foreach ($translations as $key => $value) {
            Translation::updateOrCreate(
                [
                    'instance_id' => $instanceId,
                    'locale' => $locale,
                    'group' => $group,
                    'key' => $key,
                ],
                ['value' => $value]
            );
        }

        $this->clearCache($locale, $group, $instanceId);
    }

    /**
     * Delete a translation.
     */
    public function delete(string $locale, string $group, string $key, int $instanceId = 0): bool
    {
        $deleted = Translation::where([
            'instance_id' => $instanceId,
            'locale' => $locale,
            'group' => $group,
            'key' => $key,
        ])->delete();

        $this->clearCache($locale, $group, $instanceId);
        return $deleted > 0;
    }

    /**
     * Get all groups for a locale.
     */
    public function groups(string $locale): array
    {
        return Translation::where('locale', $locale)
            ->distinct()
            ->pluck('group')
            ->toArray();
    }

    /**
     * Export all translations for a locale as group => [key => value].
     */
    public function export(string $locale, int $instanceId = 0): array
    {
        return Translation::where('locale', $locale)
            ->where(function ($q) use ($instanceId) {
                $q->where('instance_id', 0);
                if ($instanceId > 0) {
                    $q->orWhere('instance_id', $instanceId);
                }
            })
            ->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->pluck('value', 'key')->toArray())
            ->toArray();
    }

    /**
     * Import translations from array.
     */
    public function import(string $locale, array $data, int $instanceId = 0): int
    {
        $count = 0;
        foreach ($data as $group => $translations) {
            foreach ($translations as $key => $value) {
                Translation::updateOrCreate(
                    [
                        'instance_id' => $instanceId,
                        'locale' => $locale,
                        'group' => $group,
                        'key' => $key,
                    ],
                    ['value' => $value]
                );
                $count++;
            }
        }

        Cache::flush(); // Flush translation caches on import
        return $count;
    }

    protected function clearCache(string $locale, string $group, int $instanceId): void
    {
        Cache::forget("translations.{$instanceId}.{$locale}.{$group}");
        Cache::forget("translations.0.{$locale}.{$group}");
    }
}
