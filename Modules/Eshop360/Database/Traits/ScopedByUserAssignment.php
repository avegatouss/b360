<?php

namespace Modules\Eshop360\Database\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait to apply user assignment scopes on Eshop360 models.
 *
 * Models using this trait should define a $userAssignmentConfig property:
 *   protected static array $userAssignmentConfig = [
 *       ['type' => 'warehouse', 'column' => 'warehouse_id'],
 *       ['type' => 'store', 'column' => 'store_id'],
 *   ];
 *
 * Or for the Order model, use OrderUserAssignmentScope directly.
 */
trait ScopedByUserAssignment
{
    public static function bootScopedByUserAssignment(): void
    {
        if (property_exists(static::class, 'userAssignmentConfig')) {
            foreach (static::$userAssignmentConfig as $config) {
                static::addGlobalScope(
                    "user_assignment_{$config['type']}",
                    new \Modules\Eshop360\Database\Scopes\UserAssignmentScope(
                        $config['type'],
                        $config['column'],
                    ),
                );
            }
        }
    }

    /**
     * Remove all user assignment scopes for admin queries.
     */
    public static function withoutUserAssignmentScopes(): Builder
    {
        $query = static::query();

        if (property_exists(static::class, 'userAssignmentConfig')) {
            foreach (static::$userAssignmentConfig as $config) {
                $query->withoutGlobalScope("user_assignment_{$config['type']}");
            }
        }

        return $query;
    }
}
