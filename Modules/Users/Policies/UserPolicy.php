<?php

namespace Modules\Users\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.view');
    }

    public function view(User $actor, User $subject): bool
    {
        return $actor->can('users.view');
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.manage');
    }

    public function update(User $actor, User $subject): bool
    {
        return $actor->can('users.manage');
    }

    public function delete(User $actor, User $subject): bool
    {
        // prevent self-delete (optional safety)
        if ($actor->id === $subject->id) return false;

        return $actor->can('users.manage');
    }

    public function manageMembership(User $actor, User $subject): bool
    {
        return $actor->can('users.manage');
    }
}
