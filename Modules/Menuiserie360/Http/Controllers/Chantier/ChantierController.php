<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Chantier;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Menuiserie360\Domain\Chantier\Enums\StatutChantier;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Chantier\Models\EtapeChantier;

/**
 * P2-12 — Controller Chantier (lecture + avancement).
 */
final class ChantierController extends Controller
{
    public function index(Request $request): View
    {
        $chantiers = Chantier::query()
            ->when($request->statut, fn ($q, $s) => $q->where('statut', $s))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('menuiserie360::chantier.index', [
            'chantiers' => $chantiers,
            'statuts' => StatutChantier::cases(),
        ]);
    }

    public function show(string $slug, int|string $chantier): View
    {
        $chantier = Chantier::query()->with(['etapes' => fn ($q) => $q->orderBy('ordre')])->findOrFail($chantier);

        return view('menuiserie360::chantier.show', compact('chantier'));
    }

    public function avancer(Request $request, string $slug, int|string $chantier): RedirectResponse
    {
        $chantier = Chantier::findOrFail($chantier);

        $data = $request->validate([
            'etape_id' => 'required|integer',
            'avancement_pct' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $etape = EtapeChantier::where('chantier_id', $chantier->getKey())
            ->where('id', $data['etape_id'])
            ->firstOrFail();

        $etape->setAttribute('avancement_pct', $data['avancement_pct']);
        $etape->setAttribute('notes', $data['notes'] ?? null);
        if ($data['avancement_pct'] === 100) {
            $etape->setAttribute('statut', 'fait');
            $etape->setAttribute('terminee_at', now());
        } elseif ($data['avancement_pct'] > 0 && $etape->getAttribute('demarree_at') === null) {
            $etape->setAttribute('statut', 'en_cours');
            $etape->setAttribute('demarree_at', now());
        }
        $etape->save();

        return redirect()
            ->route('menuiserie.chantiers.show', ['slug' => $request->route('slug'), 'chantier' => $chantier->getKey()])
            ->with('success', 'Avancement enregistré.');
    }

    public function uploadPhoto(Request $request, string $slug, int|string $chantier): RedirectResponse
    {
        $chantier = Chantier::findOrFail($chantier);

        $request->validate([
            'photo' => 'required|file|image|max:8192',
            'legende' => 'nullable|string|max:200',
        ]);

        $chantier->addMedia($request->file('photo'))
            ->withCustomProperties(['legende' => $request->input('legende', '')])
            ->toMediaCollection('avancement');

        return redirect()
            ->route('menuiserie.chantiers.show', ['slug' => $slug, 'chantier' => $chantier->getKey()])
            ->with('success', 'Photo ajoutée.');
    }

    public function deletePhoto(string $slug, int|string $chantier, int|string $media): RedirectResponse
    {
        $chantier = Chantier::findOrFail($chantier);

        $mediaItem = $chantier->media()->findOrFail($media);
        $mediaItem->delete();

        return redirect()
            ->route('menuiserie.chantiers.show', ['slug' => $slug, 'chantier' => $chantier->getKey()])
            ->with('success', 'Photo supprimée.');
    }
}
