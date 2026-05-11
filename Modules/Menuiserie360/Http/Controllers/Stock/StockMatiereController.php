<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Stock;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Menuiserie360\Domain\Stock\Enums\CategorieMatiere;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Domain\Stock\Models\StockMatiere;
use Modules\Menuiserie360\Domain\Stock\Services\StockMatiereService;

/**
 * P2-15 — Controller Stock matière (entrées fournisseur, listing).
 */
final class StockMatiereController extends Controller
{
    public function index(Request $request): View
    {
        $matieres = MatierePremiere::query()
            ->when($request->categorie, fn ($q, $c) => $q->where('categorie', $c))
            ->when($request->search, fn ($q, $s) => $q->where('designation', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->orderBy('designation')
            ->paginate(30)
            ->withQueryString();

        return view('menuiserie360::stock.index', [
            'matieres' => $matieres,
            'categories' => CategorieMatiere::cases(),
        ]);
    }

    public function show(string $slug, int|string $matiere): View
    {
        $matiere = MatierePremiere::findOrFail($matiere);
        $stock = StockMatiere::query()
            ->where('matiere_id', $matiere->getKey())
            ->first();

        return view('menuiserie360::stock.show', compact('matiere', 'stock'));
    }

    public function recevoir(Request $request, string $slug, int|string $matiere): RedirectResponse
    {
        $matiere = MatierePremiere::findOrFail($matiere);

        $data = $request->validate([
            'quantite' => 'required|numeric|min:0.0001',
            'reference' => 'required|string|max:100',
        ]);

        app(StockMatiereService::class)->recevoir(
            (int) $matiere->getAttribute('instance_id'),
            (int) $matiere->getKey(),
            (float) $data['quantite'],
            $data['reference'],
        );

        return redirect()
            ->route('menuiserie.stocks.show', ['slug' => $request->route('slug'), 'matiere' => $matiere->getKey()])
            ->with('success', "Réception enregistrée : {$data['quantite']} {$matiere->getAttribute('unite')}.");
    }
}
