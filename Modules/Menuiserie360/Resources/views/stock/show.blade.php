<x-menuiserie360::layout title="{{ $matiere->code }} — {{ $matiere->designation }}">
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-end gap-2">
                    @can('menuiserie.stock.matiere.manage')
                        <a href="{{ route('menuiserie.stocks.edit', ['slug' => request()->route('slug'), 'matiere' => $matiere->id]) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                        <form method="POST" action="{{ route('menuiserie.stocks.destroy', ['slug' => request()->route('slug'), 'matiere' => $matiere->id]) }}" onsubmit="return confirm('Supprimer cette matière ? Action interdite si stock > 0 ou déjà utilisée dans un devis.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    @endcan
                </div>
                <div class="card-body">
                    <p><strong>Code :</strong> {{ $matiere->code }}</p>
                    <p><strong>Désignation :</strong> {{ $matiere->designation }}</p>
                    <p><strong>Catégorie :</strong> {{ $matiere->categorie }}</p>
                    <p><strong>Unité :</strong> {{ $matiere->unite }}</p>
                    <p><strong>Prix unitaire :</strong> {{ number_format((float) $matiere->prix_unitaire, 4, ',', ' ') }} XOF</p>
                    <p><strong>Seuil d'alerte :</strong> {{ $matiere->seuil_alerte }} {{ $matiere->unite }}</p>
                    <p><strong>Fournisseur :</strong> {{ $matiere->fournisseur_principal ?? '—' }}</p>
                    <p><strong>Statut :</strong>
                        @if ($matiere->is_active)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </p>
                    @if ($matiere->notes)
                        <hr/>
                        <p><strong>Notes :</strong></p>
                        <p class="text-muted small">{{ $matiere->notes }}</p>
                    @endif
                    <hr/>
                    <p><strong>Stock actuel :</strong> {{ $stock?->quantite_actuelle ?? '0' }} {{ $matiere->unite }}</p>
                    <p><strong>Réservé :</strong> {{ $stock?->quantite_reservee ?? '0' }} {{ $matiere->unite }}</p>
                    <p><strong>Disponible :</strong> {{ $stock?->quantiteDisponible() ?? 0 }} {{ $matiere->unite }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Réception fournisseur</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('menuiserie.stocks.recevoir', ['slug' => request()->route('slug'), 'matiere' => $matiere->id]) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Quantité ({{ $matiere->unite }})</label>
                            <input type="number" name="quantite" step="0.0001" min="0.0001" required class="form-control"/>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Référence (BL fournisseur)</label>
                            <input type="text" name="reference" maxlength="100" required class="form-control"/>
                        </div>
                        <button class="btn btn-primary w-100">Enregistrer la réception</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-menuiserie360::layout>
