<x-menuiserie360::layout title="Bibliothèque produits menuiserie">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Catalogue des types ({{ $types->total() }})</span>
            <a href="{{ route('menuiserie.types-produits.create', ['slug' => request()->route('slug')]) }}" class="btn btn-primary btn-sm">+ Nouveau type</a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Dimensions standard</th>
                        <th class="text-end">Prix indicatif HT</th>
                        <th>Actif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($types as $t)
                        <tr>
                            <td><strong>{{ $t->code }}</strong></td>
                            <td>{{ $t->nom }}</td>
                            <td><span class="badge bg-light text-dark">{{ $t->categorie }}</span></td>
                            <td>{{ $t->largeur_standard_mm ?? '—' }} × {{ $t->hauteur_standard_mm ?? '—' }} mm</td>
                            <td class="text-end">{{ $t->prix_indicatif_ht !== null ? number_format((float) $t->prix_indicatif_ht, 0, ',', ' ').' XOF' : '—' }}</td>
                            <td>
                                @if ($t->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('menuiserie.types-produits.show', ['slug' => request()->route('slug'), 'type' => $t->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                <a href="{{ route('menuiserie.types-produits.edit', ['slug' => request()->route('slug'), 'type' => $t->id]) }}" class="btn btn-sm btn-outline-secondary">Éditer</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted text-center">Aucun type produit. <a href="{{ route('menuiserie.types-produits.create', ['slug' => request()->route('slug')]) }}">Créer le premier</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $types->links() }}</div>
    </div>
</x-menuiserie360::layout>
