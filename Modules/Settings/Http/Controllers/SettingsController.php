<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Hooks\HookFilter;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\CurrentInstance;
use Modules\Settings\Services\SettingsManager;

final class SettingsController extends Controller
{
    public function index(string $slug, SettingsManager $settings)
    {
        $instance = CurrentInstance::get();
        $user = auth()->user();

        $registry = app(HookRegistry::class);
        $filter = app(HookFilter::class);
        $groups = $filter->filter($registry->settingsGroups(), $user, $instance);

        // Redirect to first group if available
        if ($groups->isNotEmpty()) {
            return redirect()->route('settings.group', [$instance->slug, $groups->first()->id]);
        }

        return view('settings::index', compact('instance', 'groups'));
    }

    public function group(string $slug, string $group, SettingsManager $settings)
    {
        $instance = CurrentInstance::get();
        $user = auth()->user();

        $registry = app(HookRegistry::class);
        $filter = app(HookFilter::class);
        $groups = $filter->filter($registry->settingsGroups(), $user, $instance);

        $currentGroup = $groups->firstWhere('id', $group);
        if (!$currentGroup) {
            abort(404, 'Groupe de parametres introuvable.');
        }

        $values = $settings->group($group, 0);

        return view('settings::index', compact('instance', 'groups', 'currentGroup', 'values'));
    }

    public function updateGroup(Request $request, string $slug, string $group, SettingsManager $settings)
    {
        $instance = CurrentInstance::get();

        $settingsData = $request->input('settings', []);
        $types = $request->input('types', []);

        foreach ($settingsData as $key => $value) {
            $type = $types[$key] ?? 'string';
            $settings->set("{$group}.{$key}", $value, 0, $type);
        }

        // Handle file uploads (e.g., branding images)
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $key => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store("branding/{$group}", 'public');
                    $settings->set("{$group}.{$key}", $path, 0, 'string');
                }
            }
        }

        return redirect()
            ->route('settings.group', [$instance->slug, $group])
            ->with('status', 'Parametres mis a jour.');
    }
}
