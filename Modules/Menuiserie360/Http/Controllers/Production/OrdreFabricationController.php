<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Production;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Menuiserie360\Domain\Production\Enums\StatutOrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Services\BesoinMatiereService;

/**
 * P2-9 — Interface Atelier OF.
 */
final class OrdreFabricationController extends Controller
{
    public function index(Request $request): View
    {
        $ofs = OrdreFabrication::query()
            ->when($request->statut, fn ($q, $s) => $q->where('statut', $s))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('menuiserie360::production.of.index', [
            'ofs' => $ofs,
            'statuts' => StatutOrdreFabrication::cases(),
        ]);
    }

    public function show(int|string $ofId): View
    {
        $of = OrdreFabrication::query()->with(['lignes', 'bonCommande'])->findOrFail($ofId);
        $besoinService = app(BesoinMatiereService::class);
        $disponibilite = $besoinService->verifierDisponibilite($of);

        return view('menuiserie360::production.of.show', compact('of', 'disponibilite'));
    }

    public function lancer(Request $request, int|string $ofId): RedirectResponse
    {
        $of = OrdreFabrication::findOrFail($ofId);

        // Réservation des matières via BesoinMatiereService
        app(BesoinMatiereService::class)->reserverPourOf($of);

        $of->setAttribute('statut', StatutOrdreFabrication::EN_COURS->value);
        $of->setAttribute('date_demarrage', now());
        $of->save();

        return redirect()
            ->route('menuiserie.production.show', ['slug' => $request->route('slug'), 'of' => $of->getKey()])
            ->with('success', "OF {$of->getAttribute('numero')} lancé en production.");
    }

    public function terminer(Request $request, int|string $ofId): RedirectResponse
    {
        $of = OrdreFabrication::findOrFail($ofId);

        $of->setAttribute('statut', StatutOrdreFabrication::TERMINE->value);
        $of->setAttribute('date_fin_reelle', now());
        $of->save();

        return redirect()
            ->route('menuiserie.production.show', ['slug' => $request->route('slug'), 'of' => $of->getKey()])
            ->with('success', "OF {$of->getAttribute('numero')} terminé.");
    }
}
