<x-menuiserie360::layout title="Bons de commande">
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Numéro</th><th>Client</th><th>Statut</th><th>TTC</th><th>Livraison</th><th></th></tr></thead>
                <tbody>
                @forelse ($bcs as $bc)
                    <tr>
                        <td><strong>{{ $bc->numero }}</strong></td>
                        <td>#{{ $bc->client_id }}</td>
                        <td><span class="badge bg-secondary">{{ $bc->statut }}</span></td>
                        <td>{{ number_format((float) $bc->montant_ttc, 0, ',', ' ') }} XOF</td>
                        <td>{{ optional($bc->date_livraison_prevue)->format('Y-m-d') ?? '—' }}</td>
                        <td><a href="{{ route('menuiserie.bc.show', ['slug' => request()->route('slug'), 'bc' => $bc->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center">Aucun BC.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $bcs->links() }}
        </div>
    </div>
</x-menuiserie360::layout>
