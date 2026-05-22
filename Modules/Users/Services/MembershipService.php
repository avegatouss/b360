<?php

namespace Modules\Users\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class MembershipService
{
    public function addToInstance(User $user, int $instanceId, string $status = 'active'): void
    {
        $this->assertStatus($status);

        DB::connection('system')->table('instance_user')->updateOrInsert(
            ['instance_id' => $instanceId, 'user_id' => $user->id],
            ['status' => $status, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function updateStatus(User $user, int $instanceId, string $status): void
    {
        $this->assertStatus($status);

        DB::connection('system')->table('instance_user')
            ->where('instance_id', $instanceId)
            ->where('user_id', $user->id)
            ->update(['status' => $status, 'updated_at' => now()]);
    }

    public function removeFromInstance(User $user, int $instanceId): void
    {
        DB::connection('system')->table('instance_user')
            ->where('instance_id', $instanceId)
            ->where('user_id', $user->id)
            ->delete();
    }

    public function status(User $user, int $instanceId): ?string
    {
        return DB::connection('system')->table('instance_user')
            ->where('instance_id', $instanceId)
            ->where('user_id', $user->id)
            ->value('status');
    }

    private function assertStatus(string $status): void
    {
        if (!in_array($status, ['active','invited','disabled'], true)) {
            throw new \InvalidArgumentException('Invalid membership status.');
        }
    }
}
