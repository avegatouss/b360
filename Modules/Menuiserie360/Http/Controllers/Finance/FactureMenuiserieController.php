<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Finance;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Finance\Actions\RecordPaymentAction;
use Modules\Menuiserie360\Domain\Finance\Enums\MethodePaiement;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Enums\TypeFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Http\Concerns\PreloadsCustomers;

/**
 * P2-7 part 4 — Controller Facture menuiserie (lecture seule v1).
 *
 * Création de factures = via Action interne déclenchée par listener
 * (acompte) ou commande chantier terminé (solde — P3 spec).
 */
final class FactureMenuiserieController extends Controller
{
    use PreloadsCustomers;

    public function index(Request $request): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $invoices = MenuiserieInvoice::query()
            ->when($request->statut, fn ($q, $s) => $q->where('status', $s))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->search, fn ($q, $s) => $q->where('invoice_number', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('menuiserie360::finance.factures.index', [
            'invoices' => $invoices,
            'statuts' => StatutFacture::cases(),
            'types' => TypeFacture::cases(),
            'customers' => $this->preloadCustomers($invoices->items(), $instance->id),
        ]);
    }

    public function show(string $slug, int|string $invoice): View
    {
        $invoice = MenuiserieInvoice::query()->with('payments')->findOrFail($invoice);

        return view('menuiserie360::finance.factures.show', [
            'invoice' => $invoice,
            'methodes' => MethodePaiement::cases(),
        ]);
    }

    /**
     * P3-2 — Enregistre un paiement (manual entry / Mobile Money).
     */
    public function recordPayment(
        Request $request,
        RecordPaymentAction $recordAction,
        string $slug,
        int|string $invoice,
    ): RedirectResponse {
        $invoice = MenuiserieInvoice::findOrFail($invoice);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => ['required', 'string'],
            'gateway' => 'nullable|string|max:50',
            'transaction_ref' => 'nullable|string|max:100',
            'idempotency_key' => 'nullable|string|max:128',
        ]);

        $recordAction->execute($invoice, [
            'amount' => (float) $data['amount'],
            'method' => $data['method'],
            'gateway' => $data['gateway'] ?? null,
            'transaction_ref' => $data['transaction_ref'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);

        return redirect()
            ->route('menuiserie.factures.show', ['slug' => $slug, 'invoice' => $invoice->getKey()])
            ->with('success', 'Paiement enregistré.');
    }
}
