<?php

namespace Modules\Core\Policies;

use App\Models\User;

final class InstancePolicy
{
    public function view(User $user): bool
    {
        return $user->can('instances.view');
    }

    public function manage(User $user): bool
    {
        return $user->can('instances.manage');
    }
}
