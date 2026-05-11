<x-menuiserie360::layout title="Chantiers">
    <x-menuiserie360::filter-bar
        :action="route('menuiserie.chantiers.index', ['slug' => request()->route('slug')])"
        :status-options="$statuts"
        search-placeholder="Numéro de chantier…"
    />
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Numéro</th><th>Client</th><th>BC</th><th>Statut</th><th>Début prévu</th><th>Fin prévue</th><th></th></tr></thead>
                <tbody>
                @forelse ($chantiers as $c)
                    <tr>
                        <td><strong>{{ $c->numero }}</strong></td>
                        <td>
                            @if (isset($customers[$c->client_id]))
                                <strong>{{ $customers[$c->client_id]->name }}</strong>
                            @else
                                <span class="text-muted">#{{ $c->client_id }}</span>
                            @endif
                        </td>
                        <td>
                            @if (isset($bcs[$c->bc_id]))
                                <strong>{{ $bcs[$c->bc_id]->numero }}</strong>
                            @else
                                <span class="text-muted">#{{ $c->bc_id }}</span>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $c->statut }}</span></td>
                        <td>{{ optional($c->date_debut_prevue)->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ optional($c->date_fin_prevue)->format('Y-m-d') ?? '—' }}</td>
                        <td><a href="{{ route('menuiserie.chantiers.show', ['slug' => request()->route('slug'), 'chantier' => $c->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center">Aucun chantier.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $chantiers->links() }}
        </div>
    </div>
</x-menuiserie360::layout>
