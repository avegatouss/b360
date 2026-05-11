<x-menuiserie360::layout title="Journal ventes & paiements">
    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Du</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control"/>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Au</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control"/>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Filtrer</button>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted">Ventes TTC (facturé)</h6>
                <p class="h3 mb-0">{{ number_format($totals['ventes_ttc'], 0, ',', ' ') }} XOF</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted">Encaissé</h6>
                <p class="h3 mb-0 text-success">{{ number_format($totals['encaisse'], 0, ',', ' ') }} XOF</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <h6 class="text-muted">Impayé</h6>
                <p class="h3 mb-0 text-danger">{{ number_format($totals['impaye'], 0, ',', ' ') }} XOF</p>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Factures émises ({{ $invoices->total() }})</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Date</th><th>Numéro</th><th>Client</th><th>Type</th><th>Statut</th><th class="text-end">TTC</th><th class="text-end">Payé</th><th class="text-end">Restant</th></tr></thead>
                <tbody>
                    @forelse ($invoices as $i)
                        <tr>
                            <td>{{ optional($i->issued_at)->format('Y-m-d') }}</td>
                            <td><a href="{{ route('menuiserie.factures.show', ['slug' => request()->route('slug'), 'invoice' => $i->id]) }}">{{ $i->invoice_number }}</a></td>
                            <td>#{{ $i->client_id }}</td>
                            <td><span class="badge bg-light text-dark">{{ $i->type }}</span></td>
                            <td><span class="badge bg-info">{{ $i->status }}</span></td>
                            <td class="text-end">{{ number_format((float) $i->amount_ttc, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format((float) $i->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($i->dueAmount(), 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-muted text-center">Aucune facture sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $invoices->links() }}</div>
    </div>

    <div class="card">
        <div class="card-header">Paiements encaissés ({{ $payments->total() }})</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Date</th><th>Facture</th><th>Méthode</th><th>Gateway</th><th>Réf.</th><th class="text-end">Montant</th><th>Statut</th></tr></thead>
                <tbody>
                    @forelse ($payments as $p)
                        <tr>
                            <td>{{ optional($p->paid_at)->format('Y-m-d H:i') }}</td>
                            <td>#{{ $p->payable_id }}</td>
                            <td>{{ $p->method }}</td>
                            <td>{{ $p->gateway ?? '—' }}</td>
                            <td>{{ $p->transaction_ref ?? '—' }}</td>
                            <td class="text-end">{{ number_format((float) $p->amount, 0, ',', ' ') }}</td>
                            <td><span class="badge bg-light text-dark">{{ $p->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted text-center">Aucun paiement sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $payments->links() }}</div>
    </div>
</x-menuiserie360::layout>
