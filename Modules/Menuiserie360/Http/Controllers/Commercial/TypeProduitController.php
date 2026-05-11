<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Commercial;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Commercial\Enums\CategorieProduit;
use Modules\Menuiserie360\Domain\Commercial\Models\TypeProduitMenuiserie;

/**
 * P2-C step 1 — CRUD bibliothèque types de produits menuiserie.
 *
 * Couvre P2-2 UI : édition de la bibliothèque produits standards
 * (fenêtres, baies, portes, garde-corps, vérandas).
 */
final class TypeProduitController extends Controller
{
    public function index(Request $request): View
    {
        $types = TypeProduitMenuiserie::query()
            ->when($request->categorie, fn ($q, $c) => $q->where('categorie', $c))
            ->when($request->search, fn ($q, $s) => $q->where('nom', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->orderBy('nom')
            ->paginate(30)
            ->withQueryString();

        return view('menuiserie360::commercial.types-produits.index', [
            'types' => $types,
            'categories' => CategorieProduit::cases(),
        ]);
    }

    public function show(string $slug, int|string $type): View
    {
        $type = TypeProduitMenuiserie::findOrFail($type);

        return view('menuiserie360::commercial.types-produits.show', compact('type'));
    }

    public function create(): View
    {
        return view('menuiserie360::commercial.types-produits.create', [
            'categories' => CategorieProduit::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $data = $request->validate([
            'code' => 'required|string|max:50',
            'nom' => 'required|string|max:200',
            'categorie' => ['required', 'string'],
            'description' => 'nullable|string|max:5000',
            'largeur_standard_mm' => 'nullable|integer|min:1',
            'hauteur_standard_mm' => 'nullable|integer|min:1',
            'prix_indicatif_ht' => 'nullable|numeric|min:0',
            'cout_indicatif' => 'nullable|numeric|min:0',
            'matieres_principales' => 'nullable|array',
            'matieres_principales.*.matiere_id' => 'required_with:matieres_principales|integer',
            'matieres_principales.*.qte_par_unite' => 'required_with:matieres_principales|numeric|min:0',
        ]);

        $type = TypeProduitMenuiserie::create([
            'instance_id' => $instance->id,
            'code' => $data['code'],
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'description' => $data['description'] ?? null,
            'largeur_standard_mm' => $data['largeur_standard_mm'] ?? null,
            'hauteur_standard_mm' => $data['hauteur_standard_mm'] ?? null,
            'prix_indicatif_ht' => $data['prix_indicatif_ht'] ?? null,
            'cout_indicatif' => $data['cout_indicatif'] ?? null,
            'matieres_principales' => $data['matieres_principales'] ?? null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('menuiserie.types-produits.show', ['slug' => $request->route('slug'), 'type' => $type->getKey()])
            ->with('success', "Type produit « {$type->getAttribute('nom')} » créé.");
    }

    public function edit(string $slug, int|string $type): View
    {
        $type = TypeProduitMenuiserie::findOrFail($type);

        return view('menuiserie360::commercial.types-produits.edit', [
            'type' => $type,
            'categories' => CategorieProduit::cases(),
        ]);
    }

    public function update(Request $request, string $slug, int|string $type): RedirectResponse
    {
        $type = TypeProduitMenuiserie::findOrFail($type);

        $data = $request->validate([
            'nom' => 'required|string|max:200',
            'categorie' => ['required', 'string'],
            'description' => 'nullable|string|max:5000',
            'largeur_standard_mm' => 'nullable|integer|min:1',
            'hauteur_standard_mm' => 'nullable|integer|min:1',
            'prix_indicatif_ht' => 'nullable|numeric|min:0',
            'cout_indicatif' => 'nullable|numeric|min:0',
            'matieres_principales' => 'nullable|array',
            'matieres_principales.*.matiere_id' => 'required_with:matieres_principales|integer',
            'matieres_principales.*.qte_par_unite' => 'required_with:matieres_principales|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $type->fill([
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'description' => $data['description'] ?? null,
            'largeur_standard_mm' => $data['largeur_standard_mm'] ?? null,
            'hauteur_standard_mm' => $data['hauteur_standard_mm'] ?? null,
            'prix_indicatif_ht' => $data['prix_indicatif_ht'] ?? null,
            'cout_indicatif' => $data['cout_indicatif'] ?? null,
            'matieres_principales' => $data['matieres_principales'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ])->save();

        return redirect()
            ->route('menuiserie.types-produits.show', ['slug' => $request->route('slug'), 'type' => $type->getKey()])
            ->with('success', 'Type produit mis à jour.');
    }

    public function destroy(Request $request, string $slug, int|string $type): RedirectResponse
    {
        $type = TypeProduitMenuiserie::findOrFail($type);
        $type->delete();

        return redirect()
            ->route('menuiserie.types-produits.index', ['slug' => $request->route('slug')])
            ->with('success', 'Type produit archivé.');
    }
}
