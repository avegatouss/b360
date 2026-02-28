<?php

namespace Modules\Instances\Http\Controllers;

use App\Instances\Instance;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Instances\Http\Requests\InstanceStoreRequest;
use Modules\Instances\Http\Requests\InstanceUpdateRequest;
use Modules\Instances\Services\InstanceProvisioner;

final class InstanceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, string $slug)
    {
        $currentInstance = CurrentInstance::get();

        $query = Instance::orderBy('name');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        $instances = $query->paginate(20)->withQueryString();

        // Charger les comptes de membres pour chaque instance
        $memberCounts = DB::connection('system')
            ->table('instance_user')
            ->select('instance_id', DB::raw('COUNT(*) as total'))
            ->where('status', 'active')
            ->groupBy('instance_id')
            ->pluck('total', 'instance_id');

        return view('instances::index', compact('instances', 'currentInstance', 'memberCounts'));
    }

    public function create(string $slug)
    {
        if (!setting('instances.allow_creation', true)) {
            abort(403, 'La création de nouvelles instances est désactivée.');
        }

        $currentInstance = CurrentInstance::get();
        $dbStrategy = config('app.instance_db_strategy', 'shared');
        $dbPrefix = config('app.instance_db_prefix', '');
        $dbSuffix = config('app.instance_db_suffix', '');

        return view('instances::create', compact('currentInstance', 'dbStrategy', 'dbPrefix', 'dbSuffix'));
    }

    public function store(InstanceStoreRequest $request, string $slug, InstanceProvisioner $provisioner)
    {
        if (!setting('instances.allow_creation', true)) {
            abort(403, 'La création de nouvelles instances est désactivée.');
        }

        $currentInstance = CurrentInstance::get();

        $instance = $provisioner->provision([
            'name' => $request->string('name')->toString(),
            'slug' => $request->string('slug')->toString(),
            'domain' => $request->string('domain')->toString() ?: null,
            'subdomain' => $request->string('subdomain')->toString() ?: null,
            'database' => $request->string('database')->toString() ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('instances.show', [$currentInstance->slug, $instance])
            ->with('status', "Instance « {$instance->name} » créée avec succès.");
    }

    public function show(string $slug, Instance $instance)
    {
        $currentInstance = CurrentInstance::get();

        $memberCount = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->count();

        $totalMembers = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->count();

        return view('instances::show', compact('currentInstance', 'instance', 'memberCount', 'totalMembers'));
    }

    public function edit(string $slug, Instance $instance)
    {
        $currentInstance = CurrentInstance::get();

        return view('instances::edit', compact('currentInstance', 'instance'));
    }

    public function update(InstanceUpdateRequest $request, string $slug, Instance $instance)
    {
        $currentInstance = CurrentInstance::get();

        $instance->update([
            'name' => $request->string('name')->toString(),
            'domain' => $request->string('domain')->toString() ?: null,
            'subdomain' => $request->string('subdomain')->toString() ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Instance mise à jour.');
    }

    public function destroy(string $slug, Instance $instance)
    {
        if ($instance->isRoot()) {
            abort(403, 'L\'instance root ne peut pas être supprimée.');
        }

        $currentInstance = CurrentInstance::get();

        // Supprimer les memberships
        DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->delete();

        // Supprimer les rôles Spatie pour cette instance
        $teamFk = config('permission.column_names.team_foreign_key', 'instance_id');
        DB::connection('system')
            ->table('model_has_roles')
            ->where($teamFk, $instance->id)
            ->delete();

        $instanceName = $instance->name;
        $instance->delete();

        return redirect()
            ->route('instances.index', $currentInstance->slug)
            ->with('status', "Instance « {$instanceName} » supprimée.");
    }

    public function toggle(string $slug, Instance $instance)
    {
        if ($instance->isRoot()) {
            abort(403, 'L\'instance root ne peut pas être désactivée.');
        }

        $currentInstance = CurrentInstance::get();

        $instance->update(['is_active' => !$instance->is_active]);

        $status = $instance->is_active ? 'activée' : 'désactivée';

        return back()->with('status', "Instance « {$instance->name} » {$status}.");
    }
}
