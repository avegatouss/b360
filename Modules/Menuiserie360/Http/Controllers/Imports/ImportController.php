<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Imports;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Commercial\Services\ImportDevisCsvService;
use Modules\Menuiserie360\Domain\Stock\Services\ImportMatieresCsvService;

/**
 * M-UI-9 + V1.2-3 — Imports CSV Menuiserie360.
 *
 * Surfaces :
 *   - matières premières (M-UI-9, V1.1) — updateOrCreate idempotent.
 *   - devis batch (V1.2-3) — groupement par devis_ref + skip si déjà importé.
 */
final class ImportController extends Controller
{
    public function index(): View
    {
        return view('menuiserie360::imports.index');
    }

    public function uploadMatieres(
        Request $request,
        ImportMatieresCsvService $importer,
    ): RedirectResponse {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');
        abort_if($file === null, 422, 'Fichier manquant.');

        /** @var \Illuminate\Http\UploadedFile $file */
        $result = $importer->import($file, (int) $instance->id);

        return redirect()
            ->route('menuiserie.imports.index', ['slug' => $request->route('slug')])
            ->with('import_result', $result)
            ->with('import_kind', 'matieres')
            ->with(
                $result['errors'] === [] ? 'success' : 'warning',
                $result['errors'] === []
                    ? "Import matières terminé : {$result['created']} créée(s), {$result['updated']} mise(s) à jour."
                    : "Import matières partiel : {$result['created']} créée(s), {$result['updated']} mise(s) à jour, ".count($result['errors']).' erreur(s).'
            );
    }

    public function uploadDevis(
        Request $request,
        ImportDevisCsvService $importer,
    ): RedirectResponse {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');
        abort_if($file === null, 422, 'Fichier manquant.');

        /** @var \Illuminate\Http\UploadedFile $file */
        $result = $importer->import($file, (int) $instance->id);

        return redirect()
            ->route('menuiserie.imports.index', ['slug' => $request->route('slug')])
            ->with('import_result', $result)
            ->with('import_kind', 'devis')
            ->with(
                $result['errors'] === [] ? 'success' : 'warning',
                $result['errors'] === []
                    ? "Import devis terminé : {$result['created']} créé(s), {$result['skipped']} ignoré(s), {$result['lignes_created']} ligne(s)."
                    : "Import devis partiel : {$result['created']} créé(s), {$result['skipped']} ignoré(s), ".count($result['errors']).' erreur(s).'
            );
    }
}
