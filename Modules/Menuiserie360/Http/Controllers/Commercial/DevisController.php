<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Commercial;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Commercial\Enums\StatutDevis;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Commercial\Models\LigneDevis;
use Modules\Menuiserie360\Domain\Sales\Actions\TransformDevisToBcAction;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Http\Concerns\PreloadsCustomers;

/**
 * P2-1 — Controller CRUD Devis (BC-Commercial).
 */
final class DevisController extends Controller
{
    use PreloadsCustomers;

    public function index(Request $request): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $devis = Devis::query()
            ->when($request->statut, fn ($q, $s) => $q->where('statut', $s))
            ->when($request->client_id, fn ($q, $c) => $q->where('client_id', $c))
            ->when($request->search, fn ($q, $s) => $q->where('numero', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('menuiserie360::commercial.devis.index', [
            'devis' => $devis,
            'statuts' => StatutDevis::cases(),
            'customers' => $this->preloadCustomers($devis->items(), $instance->id),
        ]);
    }

    public function show(string $slug, int|string $devis): View
    {
        $devis = Devis::query()->with('lignes')->findOrFail($devis);

        return view('menuiserie360::commercial.devis.show', compact('devis'));
    }

    public function create(): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $matieres = MatierePremiere::query()
            ->where('instance_id', $instance->id)
            ->where('is_active', true)
            ->orderBy('designation')
            ->get(['id', 'code', 'designation', 'unite', 'prix_unitaire']);

        return view('menuiserie360::commercial.devis.create', compact('matieres'));
    }

    public function store(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $data = $request->validate([
            'client_id' => 'required|integer',
            'taux_tva' => 'nullable|numeric|min:0|max:1',
            'marge_minimum' => 'nullable|numeric|min:0|max:1',
            'validite_jours' => 'nullable|integer|min:1|max:365',
            'conditions' => 'nullable|string|max:5000',
            'lignes' => 'required|array|min:1',
            'lignes.*.designation' => 'required|string|max:200',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.prix_unitaire_ht' => 'required|numeric|min:0',
            'lignes.*.cout_revient' => 'nullable|numeric|min:0',
            'lignes.*.largeur_mm' => 'nullable|integer|min:1',
            'lignes.*.hauteur_mm' => 'nullable|integer|min:1',
            'lignes.*.matiere_id' => 'nullable|integer',
        ]);

        $devis = DB::transaction(function () use ($data, $instance, $request) {
            $tauxTva = (float) ($data['taux_tva'] ?? 0.18);

            $devis = Devis::create([
                'instance_id' => $instance->id,
                'numero' => $this->generateNumero($instance->id),
                'client_id' => $data['client_id'],
                'statut' => StatutDevis::BROUILLON->value,
                'taux_tva' => $tauxTva,
                'validite_jours' => $data['validite_jours'] ?? 30,
                'marge_minimum' => $data['marge_minimum'] ?? 0.15,
                'conditions' => $data['conditions'] ?? null,
                'created_by' => $request->user()?->id,
                'montant_ht' => 0,
                'montant_tva' => 0,
                'montant_ttc' => 0,
            ]);

            $totalHt = 0.0;
            foreach ($data['lignes'] as $i => $ligne) {
                $montantHt = $ligne['prix_unitaire_ht'] * $ligne['quantite'];
                LigneDevis::create([
                    'instance_id' => $instance->id,
                    'devis_id' => $devis->getKey(),
                    'designation' => $ligne['designation'],
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire_ht' => $ligne['prix_unitaire_ht'],
                    'cout_revient' => $ligne['cout_revient'] ?? 0,
                    'largeur_mm' => $ligne['largeur_mm'] ?? null,
                    'hauteur_mm' => $ligne['hauteur_mm'] ?? null,
                    'matiere_id' => $ligne['matiere_id'] ?? null,
                    'montant_ht' => $montantHt,
                    'ordre' => $i + 1,
                ]);
                $totalHt += $montantHt;
            }

            $tva = round($totalHt * $tauxTva, 2);
            $devis->setAttribute('montant_ht', $totalHt);
            $devis->setAttribute('montant_tva', $tva);
            $devis->setAttribute('montant_ttc', round($totalHt + $tva, 2));
            $devis->save();

            return $devis;
        });

        return redirect()
            ->route('menuiserie.devis.show', ['slug' => $request->route('slug'), 'devis' => $devis->getKey()])
            ->with('success', "Devis {$devis->getAttribute('numero')} créé.");
    }

    /**
     * Marque un devis comme accepté + transforme en BonCommande
     * (déclenche aussi facture acompte via listener — workflow P2-A).
     */
    public function accepter(Request $request, string $slug, int|string $devis): RedirectResponse
    {
        $devis = Devis::findOrFail($devis);

        $data = $request->validate([
            'acompte_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        $devis->setAttribute('statut', StatutDevis::ACCEPTE->value);
        $devis->save();

        $bc = app(TransformDevisToBcAction::class)
            ->execute($devis, acomptePct: (float) ($data['acompte_pct'] ?? 30.0));

        return redirect()
            ->route('menuiserie.bc.show', ['slug' => $request->route('slug'), 'bc' => $bc->getKey()])
            ->with('success', "Devis accepté → BonCommande {$bc->getAttribute('numero')} créé.");
    }

    /**
     * Génération PDF du devis via DomPDF (P2-C step 3).
     *
     * Format A4 portrait. Le téléchargement utilise stream() pour éviter
     * de matérialiser le fichier sur disque (S3 / pas de stockage local
     * requis pour cet artefact transient).
     */
    public function pdf(string $slug, int|string $devis): Response
    {
        $devis = Devis::query()->with('lignes')->findOrFail($devis);

        return Pdf::loadView('menuiserie360::commercial.devis.pdf', compact('devis'))
            ->setPaper('A4', 'portrait')
            ->download("devis-{$devis->getAttribute('numero')}.pdf");
    }

    private function generateNumero(int $instanceId): string
    {
        $year = (int) date('Y');
        $prefix = "DEV-{$year}-";

        $last = Devis::query()
            ->where('instance_id', $instanceId)
            ->where('numero', 'like', $prefix.'%')
            ->orderByDesc('numero')
            ->value('numero');

        $sequence = 1;
        if ($last !== null) {
            $parts = explode('-', (string) $last);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('%s%04d', $prefix, $sequence);
    }
}
