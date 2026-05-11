<x-menuiserie360::layout title="Chantiers">
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Numéro</th><th>BC</th><th>Statut</th><th>Début prévu</th><th>Fin prévue</th><th></th></tr></thead>
                <tbody>
                @forelse ($chantiers as $c)
                    <tr>
                        <td><strong>{{ $c->numero }}</strong></td>
                        <td>#{{ $c->bc_id }}</td>
                        <td><span class="badge bg-secondary">{{ $c->statut }}</span></td>
                        <td>{{ optional($c->date_debut_prevue)->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ optional($c->date_fin_prevue)->format('Y-m-d') ?? '—' }}</td>
                        <td><a href="{{ route('menuiserie.chantiers.show', ['slug' => request()->route('slug'), 'chantier' => $c->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center">Aucun chantier.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $chantiers->links() }}
        </div>
    </div>
</x-menuiserie360::layout>
