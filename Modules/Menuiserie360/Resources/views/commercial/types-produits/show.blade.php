<x-menuiserie360::layout title="{{ $type->code }} — {{ $type->nom }}">
    <div class="card">
        <div class="card-body">
            <p><strong>Code :</strong> {{ $type->code }}</p>
            <p><strong>Catégorie :</strong> <span class="badge bg-light text-dark">{{ $type->categorie }}</span></p>
            <p><strong>Description :</strong> {{ $type->description ?? '—' }}</p>
            <p><strong>Dimensions standard :</strong> {{ $type->largeur_standard_mm ?? '—' }} × {{ $type->hauteur_standard_mm ?? '—' }} mm</p>
            <p><strong>Prix indicatif HT :</strong> {{ $type->prix_indicatif_ht !== null ? number_format((float) $type->prix_indicatif_ht, 2, ',', ' ').' XOF' : '—' }}</p>
            <p><strong>Coût indicatif :</strong> {{ $type->cout_indicatif !== null ? number_format((float) $type->cout_indicatif, 2, ',', ' ').' XOF' : '—' }}</p>
            <p><strong>Statut :</strong>
                @if ($type->is_active)
                    <span class="badge bg-success">Actif</span>
                @else
                    <span class="badge bg-secondary">Inactif</span>
                @endif
            </p>

            @if (! empty($type->matieres_principales))
                <h5 class="mt-4">Matières principales (auto-besoin OF)</h5>
                <table class="table table-sm">
                    <thead><tr><th>Matière #</th><th>Quantité par unité produit</th></tr></thead>
                    <tbody>
                        @foreach ($type->matieres_principales as $m)
                            <tr>
                                <td>#{{ $m['matiere_id'] ?? '—' }}</td>
                                <td>{{ $m['qte_par_unite'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="mt-4">
                <a href="{{ route('menuiserie.types-produits.edit', ['slug' => request()->route('slug'), 'type' => $type->id]) }}" class="btn btn-outline-secondary">Éditer</a>
                <form method="POST" action="{{ route('menuiserie.types-produits.destroy', ['slug' => request()->route('slug'), 'type' => $type->id]) }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger" onclick="return confirm('Archiver ce type produit ?')">Archiver</button>
                </form>
            </div>
        </div>
    </div>
</x-menuiserie360::layout>
