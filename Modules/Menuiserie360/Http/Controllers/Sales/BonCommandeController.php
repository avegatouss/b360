<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Sales;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Sales\Enums\StatutBonCommande;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;
use Modules\Menuiserie360\Http\Concerns\PreloadsCustomers;

/**
 * P2-5 — Controller BonCommande (lecture seule v1, transformations
 * gérées par le workflow Devis→BC + actions Production).
 */
final class BonCommandeController extends Controller
{
    use PreloadsCustomers;

    public function index(Request $request): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $bcs = BonCommande::query()
            ->when($request->statut, fn ($q, $s) => $q->where('statut', $s))
            ->when($request->client_id, fn ($q, $c) => $q->where('client_id', $c))
            ->when($request->search, fn ($q, $s) => $q->where('numero', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('menuiserie360::sales.bc.index', [
            'bcs' => $bcs,
            'statuts' => StatutBonCommande::cases(),
            'customers' => $this->preloadCustomers($bcs->items(), $instance->id),
        ]);
    }

    public function show(string $slug, int|string $bc): View
    {
        $bc = BonCommande::query()->with(['items', 'devis', 'factureAcompte'])->findOrFail($bc);

        return view('menuiserie360::sales.bc.show', compact('bc'));
    }
}
