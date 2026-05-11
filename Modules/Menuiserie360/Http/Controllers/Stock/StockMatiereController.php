<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Stock;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Commercial\Models\LigneDevis;
use Modules\Menuiserie360\Domain\Stock\Enums\CategorieMatiere;
use Modules\Menuiserie360\Domain\Stock\Enums\UniteMesure;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Domain\Stock\Models\StockMatiere;
use Modules\Menuiserie360\Domain\Stock\Services\StockMatiereService;

/**
 * P2-15 / M-UI-2 — Controller Stock matière : listing, lecture, réception
 * fournisseur, et CRUD complet du catalogue matières premières.
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

    public function create(): View
    {
        return view('menuiserie360::stock.create', [
            'categories' => CategorieMatiere::cases(),
            'unites' => UniteMesure::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $data = $this->validatePayload($request, instanceId: (int) $instance->id);

        $matiere = MatierePremiere::create([
            'instance_id' => $instance->id,
            ...$data,
        ]);

        return redirect()
            ->route('menuiserie.stocks.show', ['slug' => $request->route('slug'), 'matiere' => $matiere->getKey()])
            ->with('success', "Matière {$matiere->getAttribute('code')} créée.");
    }

    public function edit(string $slug, int|string $matiere): View
    {
        $matiere = MatierePremiere::findOrFail($matiere);

        return view('menuiserie360::stock.edit', [
            'matiere' => $matiere,
            'categories' => CategorieMatiere::cases(),
            'unites' => UniteMesure::cases(),
        ]);
    }

    public function update(Request $request, string $slug, int|string $matiere): RedirectResponse
    {
        $matiere = MatierePremiere::findOrFail($matiere);
        $instanceId = (int) $matiere->getAttribute('instance_id');

        $data = $this->validatePayload($request, instanceId: $instanceId, ignoreId: (int) $matiere->getKey());

        $matiere->fill($data)->save();

        return redirect()
            ->route('menuiserie.stocks.show', ['slug' => $slug, 'matiere' => $matiere->getKey()])
            ->with('success', "Matière {$matiere->getAttribute('code')} mise à jour.");
    }

    public function destroy(string $slug, int|string $matiere): RedirectResponse
    {
        $matiere = MatierePremiere::findOrFail($matiere);

        // Garde-fou 1 : interdit si stock > 0 (entrée/réservation en cours).
        $stock = StockMatiere::query()
            ->where('matiere_id', $matiere->getKey())
            ->first();
        if ($stock !== null && ((float) $stock->getAttribute('quantite_actuelle') > 0 || (float) $stock->getAttribute('quantite_reservee') > 0)) {
            return redirect()
                ->route('menuiserie.stocks.show', ['slug' => $slug, 'matiere' => $matiere->getKey()])
                ->with('error', 'Impossible de supprimer : stock non nul. Sortir le stock avant suppression.');
        }

        // Garde-fou 2 : interdit si référencée par au moins une ligne de devis.
        $usedInDevis = LigneDevis::query()
            ->where('matiere_id', $matiere->getKey())
            ->exists();
        if ($usedInDevis) {
            return redirect()
                ->route('menuiserie.stocks.show', ['slug' => $slug, 'matiere' => $matiere->getKey()])
                ->with('error', 'Impossible de supprimer : matière référencée dans un devis existant. Désactiver à la place.');
        }

        $code = (string) $matiere->getAttribute('code');
        $matiere->delete();

        return redirect()
            ->route('menuiserie.stocks.index', ['slug' => $slug])
            ->with('success', "Matière {$code} supprimée.");
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

    /**
     * Règles de validation partagées entre store() et update().
     *
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, int $instanceId, ?int $ignoreId = null): array
    {
        $codeUniqueRule = Rule::unique('mnu_matieres_premieres', 'code')
            ->where(fn ($q) => $q->where('instance_id', $instanceId)->whereNull('deleted_at'));
        if ($ignoreId !== null) {
            $codeUniqueRule = $codeUniqueRule->ignore($ignoreId);
        }

        $categories = array_map(static fn (CategorieMatiere $c) => $c->value, CategorieMatiere::cases());
        $unites = array_map(static fn (UniteMesure $u) => $u->value, UniteMesure::cases());

        return $request->validate([
            'code' => ['required', 'string', 'max:50', $codeUniqueRule],
            'designation' => ['required', 'string', 'max:200'],
            'categorie' => ['required', Rule::in($categories)],
            'unite' => ['required', Rule::in($unites)],
            'prix_unitaire' => ['required', 'numeric', 'min:0'],
            'seuil_alerte' => ['required', 'numeric', 'min:0'],
            'fournisseur_principal' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
