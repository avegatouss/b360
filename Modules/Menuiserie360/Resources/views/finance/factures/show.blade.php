<x-menuiserie360::layout title="Facture {{ $invoice->invoice_number }}">
    <div class="card">
        <div class="card-body">
            <p><strong>Type :</strong> {{ $invoice->type }}</p>
            <p><strong>Statut :</strong> <span class="badge bg-info">{{ $invoice->status }}</span></p>
            <p><strong>Client :</strong> #{{ $invoice->client_id }}</p>
            <p><strong>BC :</strong> {{ $invoice->bc_id ? '#'.$invoice->bc_id : '—' }}</p>
            <p><strong>Émise le :</strong> {{ optional($invoice->issued_at)->format('Y-m-d') ?? '—' }}</p>

            <hr/>

            <p><strong>HT :</strong> {{ number_format((float) $invoice->amount_ht, 2, ',', ' ') }} XOF</p>
            <p><strong>TVA ({{ (float) $invoice->tax_rate * 100 }}%) :</strong> {{ number_format((float) $invoice->amount_tva, 2, ',', ' ') }} XOF</p>
            <p><strong>TTC :</strong> {{ number_format((float) $invoice->amount_ttc, 2, ',', ' ') }} XOF</p>
            <p><strong>Payé :</strong> {{ number_format((float) $invoice->paid_amount, 2, ',', ' ') }} XOF</p>
            <p><strong>Restant dû :</strong> {{ number_format($invoice->dueAmount(), 2, ',', ' ') }} XOF</p>

            @if ($invoice->status !== 'paid_full' && $invoice->status !== 'cancelled')
                <hr/>
                <h5>Enregistrer un paiement</h5>
                <form method="POST" action="{{ route('menuiserie.factures.payments.store', ['slug' => request()->route('slug'), 'invoice' => $invoice->id]) }}" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small">Montant (XOF)</label>
                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $invoice->dueAmount() }}" required class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Méthode</label>
                        <select name="method" required class="form-select">
                            @foreach ($methodes as $m)
                                <option value="{{ $m->value }}">{{ $m->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Gateway (Mobile Money)</label>
                        <select name="gateway" class="form-select">
                            <option value="">—</option>
                            <option value="cinetpay">CinetPay</option>
                            <option value="mtn_momo">MTN MoMo</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="wave">Wave</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Réf. transaction</label>
                        <input type="text" name="transaction_ref" maxlength="100" class="form-control"/>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">Enregistrer</button>
                    </div>
                </form>
            @endif

            <h5 class="mt-4">Paiements ({{ $invoice->payments->count() }})</h5>
            @if ($invoice->payments->count() === 0)
                <p class="text-muted">Aucun paiement enregistré.</p>
            @else
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Méthode</th><th>Référence</th><th class="text-end">Montant</th><th>Statut</th></tr></thead>
                    <tbody>
                        @foreach ($invoice->payments as $p)
                            <tr>
                                <td>{{ optional($p->paid_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>{{ $p->method }}</td>
                                <td>{{ $p->transaction_ref ?? '—' }}</td>
                                <td class="text-end">{{ number_format((float) $p->amount, 2, ',', ' ') }}</td>
                                <td><span class="badge bg-light text-dark">{{ $p->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-menuiserie360::layout>
