<?php

namespace App\Models\Concerns;

/**
 * Backward-compatible alias for the canonical trait.
 *
 * All models should import Modules\Core\Database\Traits\BelongsToInstance
 * directly.  This file is kept only so that any legacy code referencing
 * App\Models\Concerns\BelongsToInstance continues to work.
 */
trait BelongsToInstance
{
    use \Modules\Core\Database\Traits\BelongsToInstance;
}
