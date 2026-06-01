<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Chantier;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Chantier\Enums\StatutChantier;
use Modules\Menuiserie360\Domain\Chantier\Events\ChantierTermine;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Chantier\Models\EtapeChantier;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;
use Modules\Menuiserie360\Http\Concerns\PreloadsCustomers;

/**
 * P2-12 — Controller Chantier (lecture + avancement).
 */
final class ChantierController extends Controller
{
    use PreloadsCustomers;

    public function index(Request $request): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $chantiers = Chantier::query()
            ->when($request->statut, fn ($q, $s) => $q->where('statut', $s))
            ->when($request->search, fn ($q, $s) => $q->where('numero', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Préchargement BC pour afficher le numéro humain au lieu de #id.
        $bcIds = array_values(array_filter(array_map(
            static fn ($c) => $c->getAttribute('bc_id'),
            $chantiers->items()
        )));
        $bcsByid = $bcIds === [] ? [] : BonCommande::query()->whereIn('id', $bcIds)->get()->keyBy('id');

        return view('menuiserie360::chantier.index', [
            'chantiers' => $chantiers,
            'statuts' => StatutChantier::cases(),
            'customers' => $this->preloadCustomers($chantiers->items(), $instance->id),
            'bcs' => $bcsByid,
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

    /**
     * P3-1 — Clôture d'un chantier (statut → TERMINE) + dispatch
     * de l'event ChantierTermine qui déclenche la facture de solde.
     *
     * Idempotent : si le chantier est déjà TERMINE, l'event n'est pas
     * re-dispatché (la facture solde existante serait de toute façon
     * court-circuitée par CreateMenuiserieInvoiceAction::executeSolde).
     */
    public function terminer(Request $request, string $slug, int|string $chantier): RedirectResponse
    {
        $chantier = Chantier::findOrFail($chantier);

        if ($chantier->getAttribute('statut') === StatutChantier::TERMINE->value) {
            return redirect()
                ->route('menuiserie.chantiers.show', ['slug' => $slug, 'chantier' => $chantier->getKey()])
                ->with('info', 'Chantier déjà clôturé.');
        }

        DB::transaction(function () use ($chantier) {
            $chantier->setAttribute('statut', StatutChantier::TERMINE->value);
            $chantier->setAttribute('date_fin_reelle', now());
            $chantier->save();

            ChantierTermine::dispatch(
                (int) $chantier->getAttribute('instance_id'),
                (int) $chantier->getKey(),
                (int) $chantier->getAttribute('bc_id'),
                (int) $chantier->getAttribute('client_id'),
            );
        });

        return redirect()
            ->route('menuiserie.chantiers.show', ['slug' => $slug, 'chantier' => $chantier->getKey()])
            ->with('success', 'Chantier clôturé. Facture de solde générée.');
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
