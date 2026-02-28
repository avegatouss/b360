<?php

namespace Modules\Settings\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SettingsManager
{
    private const CACHE_PREFIX = 'b360.settings';
    private const CACHE_TTL = 300;

    public function get(string $key, mixed $default = null, ?int $instanceId = null): mixed
    {
        $group = $this->extractGroup($key);
        $settingKey = $this->extractKey($key);

        if ($instanceId !== null && $instanceId > 0) {
            $value = $this->fetch($instanceId, $group, $settingKey);
            if ($value !== null) {
                return $value;
            }
        }

        $value = $this->fetch(0, $group, $settingKey);

        return $value ?? $default;
    }

    public function set(string $key, mixed $value, int $instanceId = 0, string $type = 'string'): void
    {
        $group = $this->extractGroup($key);
        $settingKey = $this->extractKey($key);

        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        DB::connection('system')->table('settings')->updateOrInsert(
            [
                'instance_id' => $instanceId,
                'group' => $group,
                'key' => $settingKey,
            ],
            [
                'value' => (string) $value,
                'type' => $type,
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        $this->clearCache($instanceId, $group);
    }

    public function group(string $group, int $instanceId = 0): array
    {
        $cacheKey = self::CACHE_PREFIX . ".{$instanceId}.{$group}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group, $instanceId) {
            $rows = DB::connection('system')
                ->table('settings')
                ->where('instance_id', $instanceId)
                ->where('group', $group)
                ->get();

            $result = [];
            foreach ($rows as $row) {
                $result[$row->key] = match ($row->type) {
                    'boolean' => filter_var($row->value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $row->value,
                    'json' => json_decode($row->value, true),
                    default => $row->value,
                };
            }

            return $result;
        });
    }

    public function clearCache(int $instanceId = 0, ?string $group = null): void
    {
        if ($group !== null) {
            Cache::forget(self::CACHE_PREFIX . ".{$instanceId}.{$group}");
        }
    }

    private function extractGroup(string $key): string
    {
        $parts = explode('.', $key, 2);
        return count($parts) === 2 ? $parts[0] : 'general';
    }

    private function extractKey(string $key): string
    {
        $parts = explode('.', $key, 2);
        return $parts[1] ?? $parts[0];
    }

    private function fetch(int $instanceId, string $group, string $key): mixed
    {
        $row = DB::connection('system')
            ->table('settings')
            ->where('instance_id', $instanceId)
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        if (!$row) {
            return null;
        }

        return match ($row->type) {
            'boolean' => filter_var($row->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $row->value,
            'json' => json_decode($row->value, true),
            default => $row->value,
        };
    }
}
