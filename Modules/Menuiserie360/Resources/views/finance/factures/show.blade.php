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
