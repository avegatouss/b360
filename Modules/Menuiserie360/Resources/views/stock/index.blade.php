<x-menuiserie360::layout title="Stock matières premières">
    <x-menuiserie360::filter-bar
        :action="route('menuiserie.stocks.index', ['slug' => request()->route('slug')])"
        :status-options="$categories"
        status-param="categorie"
        status-label="Catégorie"
        search-placeholder="Code ou désignation…"
    />
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Catalogue matières</span>
            @can('menuiserie.stock.matiere.manage')
                <a href="{{ route('menuiserie.stocks.create', ['slug' => request()->route('slug')]) }}" class="btn btn-primary btn-sm">+ Nouvelle matière</a>
            @endcan
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Désignation</th>
                        <th>Catégorie</th>
                        <th>Unité</th>
                        <th class="text-end">Prix unitaire</th>
                        <th class="text-end">Seuil alerte</th>
                        <th>Statut</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($matieres as $m)
                    <tr>
                        <td><strong>{{ $m->code }}</strong></td>
                        <td>{{ $m->designation }}</td>
                        <td>{{ $m->categorie }}</td>
                        <td>{{ $m->unite }}</td>
                        <td class="text-end">{{ number_format((float) $m->prix_unitaire, 4, ',', ' ') }}</td>
                        <td class="text-end">{{ $m->seuil_alerte }}</td>
                        <td>
                            @if ($m->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('menuiserie.stocks.show', ['slug' => request()->route('slug'), 'matiere' => $m->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                            @can('menuiserie.stock.matiere.manage')
                                <a href="{{ route('menuiserie.stocks.edit', ['slug' => request()->route('slug'), 'matiere' => $m->id]) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted text-center">Aucune matière au catalogue. @can('menuiserie.stock.matiere.manage')<a href="{{ route('menuiserie.stocks.create', ['slug' => request()->route('slug')]) }}">Créer la première matière</a>.@endcan</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $matieres->links() }}
        </div>
    </div>
</x-menuiserie360::layout>
