<?php

namespace Modules\ModuleManager\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Modules\ModuleManager;
use Modules\Core\Support\CurrentInstance;
use Modules\ModuleManager\Services\ModuleInstaller;
use Nwidart\Modules\Facades\Module;

final class ModuleController extends Controller
{
    use AuthorizesRequests;

    protected const PROTECTED_MODULES = [
        'Core', 'Auth', 'Dashboard', 'Installer', 'ModuleManager', 'Instances', 'Settings',
    ];

    public function index(string $slug)
    {
        $instance = CurrentInstance::get();

        $nwidartModules = Module::all();

        $dbModules = DB::connection('system')
            ->table('modules')
            ->get()
            ->keyBy(fn ($m) => strtolower($m->name));

        $modules = collect($nwidartModules)->map(function ($mod) use ($dbModules) {
            $name = $mod->getName();
            $json = $this->readModuleJson($mod);
            $dbRecord = $dbModules->get(strtolower($name));

            return (object) [
                'name' => $name,
                'alias' => $json['alias'] ?? strtolower($name),
                'description' => $json['description'] ?? '',
                'version' => $json['version'] ?? '1.0.0',
                'is_enabled' => $mod->isEnabled(),
                'is_protected' => in_array($name, self::PROTECTED_MODULES, true),
                'db_enabled' => $dbRecord?->is_enabled ?? false,
                'meta' => json_decode($dbRecord->meta ?? '{}', true),
            ];
        })->sortBy('name')->values();

        return view('module-manager::index', compact('modules', 'instance'));
    }

    public function show(string $slug, string $name)
    {
        $instance = CurrentInstance::get();

        $mod = Module::find($name);
        if (! $mod) {
            abort(404, 'Module introuvable.');
        }

        $json = $this->readModuleJson($mod);
        $isProtected = in_array($name, self::PROTECTED_MODULES, true);

        $dbRecord = DB::connection('system')
            ->table('modules')
            ->where('name', $name)
            ->first();

        $readme = null;
        $readmePath = $mod->getPath().'/README.md';
        if (file_exists($readmePath)) {
            $readme = Str::markdown(file_get_contents($readmePath));
        }

        return view('module-manager::show', compact('instance', 'mod', 'json', 'isProtected', 'dbRecord', 'readme'));
    }

    public function toggle(string $slug, string $name)
    {
        if (in_array($name, self::PROTECTED_MODULES, true)) {
            abort(403, 'Ce module est protégé et ne peut pas être désactivé.');
        }

        $mod = Module::find($name);
        if (! $mod) {
            abort(404, 'Module introuvable.');
        }

        $instance = CurrentInstance::get();

        if ($mod->isEnabled()) {
            $mod->disable();
            DB::connection('system')
                ->table('modules')
                ->where('name', $name)
                ->update(['is_enabled' => false, 'updated_at' => now()]);
            $message = "Module « {$name} » désactivé.";
        } else {
            $mod->enable();
            DB::connection('system')
                ->table('modules')
                ->updateOrInsert(
                    ['name' => $name],
                    ['is_enabled' => true, 'updated_at' => now(), 'created_at' => now()]
                );
            $message = "Module « {$name} » activé.";

            // R-401-FIX S6 — Le module fraîchement activé peut déclarer une
            // route de redirection (wizard de setup, page d'init, …) via
            // HookRegistry::addPostEnableRedirect (cf. Eshop360HooksProvider).
            // Le ModuleController est désormais agnostique au nom du module.
            $redirect = app(HookRegistry::class)->postEnableRedirect($name);
            if ($redirect !== null) {
                $shouldRedirect = $redirect->condition === null
                    || (bool) ($redirect->condition)($instance);
                if ($shouldRedirect) {
                    app(ModuleManager::class)->clearCache();

                    return redirect()
                        ->route($redirect->route, $instance->slug)
                        ->with('status', "Module « {$name} » activé. Configurez l'espace dédié.");
                }
            }
        }

        app(ModuleManager::class)->clearCache();

        return redirect()
            ->route('modules.index', $instance->slug)
            ->with('status', $message);
    }

    public function upload(Request $request, string $slug, ModuleInstaller $installer)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $instance = CurrentInstance::get();

        $moduleName = $installer->installFromZip($request->file('file'));

        return redirect()
            ->route('modules.index', $instance->slug)
            ->with('status', "Module « {$moduleName} » installé avec succès.");
    }

    public function destroy(string $slug, string $name)
    {
        if (in_array($name, self::PROTECTED_MODULES, true)) {
            abort(403, 'Ce module est protégé et ne peut pas être supprimé.');
        }

        $mod = Module::find($name);
        if (! $mod) {
            abort(404, 'Module introuvable.');
        }

        $instance = CurrentInstance::get();

        if ($mod->isEnabled()) {
            $mod->disable();
        }

        $mod->delete();

        DB::connection('system')
            ->table('modules')
            ->where('name', $name)
            ->delete();

        app(ModuleManager::class)->clearCache();

        return redirect()
            ->route('modules.index', $instance->slug)
            ->with('status', "Module « {$name} » supprimé.");
    }

    private function readModuleJson($mod): array
    {
        $path = $mod->getPath().'/module.json';

        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?: [];
    }
}
