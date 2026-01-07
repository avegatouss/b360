<?php

namespace Modules\Core\Support;

use App\Instances\Instance;

final class CurrentInstance
{
    public static function get(): ?Instance
    {
        /** @var Instance|null $instance */
        $instance = app()->bound('currentInstance') ? app('currentInstance') : null;
        return $instance instanceof Instance ? $instance : null;
    }

    public static function set(?Instance $instance): void
    {
        app()->instance('currentInstance', $instance);
    }
}
