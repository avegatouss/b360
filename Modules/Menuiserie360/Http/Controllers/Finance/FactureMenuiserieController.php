<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Finance;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Enums\TypeFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;

/**
 * P2-7 part 4 — Controller Facture menuiserie (lecture seule v1).
 *
 * Création de factures = via Action interne déclenchée par listener
 * (acompte) ou commande chantier terminé (solde — P3 spec).
 */
final class FactureMenuiserieController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = MenuiserieInvoice::query()
            ->when($request->statut, fn ($q, $s) => $q->where('status', $s))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('menuiserie360::finance.factures.index', [
            'invoices' => $invoices,
            'statuts' => StatutFacture::cases(),
            'types' => TypeFacture::cases(),
        ]);
    }

    public function show(string $slug, int|string $invoice): View
    {
        $invoice = MenuiserieInvoice::query()->with('payments')->findOrFail($invoice);

        return view('menuiserie360::finance.factures.show', compact('invoice'));
    }
}
