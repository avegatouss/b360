<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ThemeController extends Controller
{
    public const THEMES = ['default', 'dark', 'blue', 'green', 'red'];

    /**
     * Show available themes.
     */
    public function index()
    {
        $currentTheme = session('theme', 'default');
        $themes = self::THEMES;

        return view('core::admin.themes', compact('currentTheme', 'themes'));
    }

    /**
     * Switch the active theme.
     */
    public function switch(Request $request)
    {
        $request->validate([
            'theme' => ['required', 'string', 'in:' . implode(',', self::THEMES)],
        ]);

        $theme = $request->input('theme');
        session(['theme' => $theme]);

        // Store in user preference if authenticated
        if (auth()->check() && function_exists('setting')) {
            $manager = app(\Modules\Settings\Services\SettingsManager::class);
            $manager->set('user.theme_' . auth()->id(), $theme, 0, 'string');
        }

        return back()->with('status', 'Theme mis a jour.');
    }
}
