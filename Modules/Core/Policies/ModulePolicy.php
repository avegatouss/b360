<?php

namespace Modules\Core\Policies;

use App\Models\User;

final class ModulePolicy
{
    public function view(User $user): bool
    {
        return $user->can('modules.view');
    }

    public function manage(User $user): bool
    {
        return $user->can('modules.manage');
    }
}
