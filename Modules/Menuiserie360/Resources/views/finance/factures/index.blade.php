<x-menuiserie360::layout title="Factures menuiserie">
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Numéro</th><th>Type</th><th>BC</th><th>Statut</th><th class="text-end">TTC</th><th class="text-end">Restant dû</th><th></th></tr></thead>
                <tbody>
                @forelse ($invoices as $inv)
                    <tr>
                        <td><strong>{{ $inv->invoice_number }}</strong></td>
                        <td><span class="badge bg-light text-dark">{{ $inv->type }}</span></td>
                        <td>{{ $inv->bc_id ? '#'.$inv->bc_id : '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $inv->status }}</span></td>
                        <td class="text-end">{{ number_format((float) $inv->amount_ttc, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($inv->dueAmount(), 0, ',', ' ') }}</td>
                        <td><a href="{{ route('menuiserie.factures.show', ['slug' => request()->route('slug'), 'invoice' => $inv->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center">Aucune facture.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $invoices->links() }}
        </div>
    </div>
</x-menuiserie360::layout>
