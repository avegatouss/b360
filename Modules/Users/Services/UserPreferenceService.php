<?php

namespace Modules\Users\Services;

use Modules\Users\Models\UserPreference;

class UserPreferenceService
{
    /**
     * Default preference values.
     */
    public const DEFAULTS = [
        'language'            => 'fr',
        'theme'               => 'light',
        'timezone'            => 'Africa/Douala',
        'date_format'         => 'd/m/Y',
        'notifications_email' => '1',
        'notifications_sms'   => '0',
        'items_per_page'      => '20',
    ];

    /**
     * Get a single preference value.
     */
    public function get(int $userId, string $key, $default = null)
    {
        $pref = UserPreference::where('user_id', $userId)
            ->where('key', $key)
            ->first();

        if ($pref) {
            return $pref->value;
        }

        return $default ?? (self::DEFAULTS[$key] ?? null);
    }

    /**
     * Set a single preference value.
     */
    public function set(int $userId, string $key, $value): void
    {
        UserPreference::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value],
        );
    }

    /**
     * Get all preferences for a user (merged with defaults).
     */
    public function all(int $userId): array
    {
        $stored = UserPreference::where('user_id', $userId)
            ->pluck('value', 'key')
            ->all();

        return array_merge(self::DEFAULTS, $stored);
    }

    /**
     * Set multiple preferences at once.
     */
    public function setMany(int $userId, array $preferences): void
    {
        foreach ($preferences as $key => $value) {
            $this->set($userId, $key, $value);
        }
    }
}
