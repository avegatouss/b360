<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Instances\Instance;

final class LoginRedirector
{
    /**
     * @return array{count:int, instances:array<int, array{slug:string,id:int}>}
     */
    public function activeInstances(User $user): array
    {
        $rows = DB::connection('system')
            ->table('instances')
            ->join('instance_user', 'instances.id', '=', 'instance_user.instance_id')
            ->where('instance_user.user_id', $user->id)
            ->where('instance_user.status', 'active')
            ->where('instances.is_active', true)
            ->select(['instances.id','instances.slug'])
            ->orderBy('instances.slug')
            ->get();

        $list = $rows->map(fn($r) => ['id' => (int)$r->id, 'slug' => (string)$r->slug])->all();

        return ['count' => count($list), 'instances' => $list];
    }

    public function redirectAfterGlobalLogin(User $user)
    {
        $data = $this->activeInstances($user);

        if ($data['count'] === 0) {
            return redirect()->route('instances.no_active');
        }

        if ($data['count'] === 1) {
            return redirect()->to('/i/' . $data['instances'][0]['slug']);
        }

        return redirect()->route('instances.select');
    }

    public function redirectAfterInstanceLogin(User $user, Instance $instance)
    {
        return redirect()->to('/i/' . $instance->slug);
    }
}
