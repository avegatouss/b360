<x-menuiserie360::layout title="Bon de commande {{ $bc->numero }}">
    <div class="card">
        <div class="card-body">
            <p><strong>Statut :</strong> <span class="badge bg-info">{{ $bc->statut }}</span></p>
            <p><strong>Devis source :</strong> #{{ $bc->devis_id }}</p>
            <p><strong>Client :</strong> #{{ $bc->client_id }}</p>
            <p><strong>Montant TTC :</strong> {{ number_format((float) $bc->montant_ttc, 2, ',', ' ') }} XOF</p>
            <p><strong>Acompte ({{ (float) $bc->acompte_pct }}%) :</strong> {{ number_format($bc->montantAcompte(), 2, ',', ' ') }} XOF</p>
            @if ($bc->facture_acompte_id)
                <p><strong>Facture acompte :</strong> #{{ $bc->facture_acompte_id }}</p>
            @endif

            <h5 class="mt-4">Items ({{ $bc->items->count() }})</h5>
            <table class="table table-sm">
                <thead><tr><th>Désignation</th><th>Dimensions</th><th>Qté</th><th class="text-end">Total HT</th></tr></thead>
                <tbody>
                @foreach ($bc->items as $item)
                    <tr>
                        <td>{{ $item->designation }}</td>
                        <td>{{ $item->largeur_mm ?? '-' }} × {{ $item->hauteur_mm ?? '-' }} mm</td>
                        <td>{{ $item->quantite }}</td>
                        <td class="text-end">{{ number_format((float) $item->montant_ht, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-menuiserie360::layout>
