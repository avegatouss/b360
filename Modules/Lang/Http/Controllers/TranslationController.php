<?php

namespace Modules\Lang\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lang\Models\Translation;
use Modules\Lang\Services\TranslationRepository;
use Modules\Lang\Services\LocaleManager;

class TranslationController extends Controller
{
    public function __construct(
        private TranslationRepository $repository,
        private LocaleManager $localeManager,
    ) {}

    /**
     * Translation management index.
     */
    public function index(Request $request)
    {
        $locale = $request->get('locale', $this->localeManager->current());
        $group = $request->get('group');

        $query = Translation::forLocale($locale);

        if ($group) {
            $query->forGroup($group);
        }

        $translations = $query->orderBy('group')->orderBy('key')->paginate(50);
        $groups = $this->repository->groups($locale);
        $locales = $this->localeManager->supported();

        return view('lang::translations.index', compact('translations', 'groups', 'locales', 'locale', 'group'));
    }

    /**
     * Store or update a translation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|max:10',
            'group' => 'required|string|max:100',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
            'instance_id' => 'integer',
        ]);

        $this->repository->set(
            $validated['locale'],
            $validated['group'],
            $validated['key'],
            $validated['value'],
            $validated['instance_id'] ?? 0,
        );

        return redirect()->back()->with('success', 'Translation saved.');
    }

    /**
     * Bulk update translations.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|max:10',
            'group' => 'required|string|max:100',
            'translations' => 'required|array',
            'translations.*' => 'nullable|string',
        ]);

        $this->repository->bulkSet(
            $validated['locale'],
            $validated['group'],
            $validated['translations'],
            $request->integer('instance_id', 0),
        );

        return redirect()->back()->with('success', 'Translations updated.');
    }

    /**
     * Delete a translation.
     */
    public function destroy(Request $request, Translation $translation)
    {
        $translation->delete();
        return redirect()->back()->with('success', 'Translation deleted.');
    }

    /**
     * Export translations as JSON.
     */
    public function export(Request $request)
    {
        $locale = $request->get('locale', $this->localeManager->current());
        $instanceId = $request->integer('instance_id', 0);

        $data = $this->repository->export($locale, $instanceId);

        return response()->json($data);
    }

    /**
     * Import translations from JSON.
     */
    public function import(Request $request)
    {
        $request->validate([
            'locale' => 'required|string',
            'file' => 'required|file|mimes:json',
        ]);

        $data = json_decode(file_get_contents($request->file('file')->getRealPath()), true);

        if (!is_array($data)) {
            return redirect()->back()->withErrors(['file' => 'Invalid JSON file.']);
        }

        $count = $this->repository->import(
            $request->locale,
            $data,
            $request->integer('instance_id', 0),
        );

        return redirect()->back()->with('success', "{$count} translations imported.");
    }
}
