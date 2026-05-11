<x-menuiserie360::layout title="Devis menuiserie">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Liste des devis</span>
            <a href="{{ route('menuiserie.devis.create', ['slug' => request()->route('slug')]) }}" class="btn btn-primary btn-sm">+ Nouveau devis</a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Statut</th>
                        <th class="text-end">Montant TTC</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($devis as $d)
                        <tr>
                            <td><strong>{{ $d->numero }}</strong></td>
                            <td>#{{ $d->client_id }}</td>
                            <td><span class="badge bg-secondary">{{ $d->statut }}</span></td>
                            <td class="text-end">{{ number_format((float) $d->montant_ttc, 0, ',', ' ') }} XOF</td>
                            <td>{{ $d->created_at?->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('menuiserie.devis.show', ['slug' => request()->route('slug'), 'devis' => $d->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted text-center">Aucun devis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $devis->links() }}</div>
    </div>
</x-menuiserie360::layout>
