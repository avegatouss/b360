<x-menuiserie360::layout title="Dashboard opérationnel">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <strong>OFs à lancer</strong>
                    <span class="badge bg-warning text-dark">{{ $ofsEnAttente->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Numéro</th><th>BC</th><th>Créé</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($ofsEnAttente as $of)
                                <tr>
                                    <td><strong>{{ $of->numero }}</strong></td>
                                    <td>#{{ $of->bc_id }}</td>
                                    <td>{{ optional($of->created_at)->format('Y-m-d') }}</td>
                                    <td><a href="{{ route('menuiserie.production.show', ['slug' => request()->route('slug'), 'of' => $of->id]) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center">Aucun OF en attente.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <strong>OFs en cours de fabrication</strong>
                    <span class="badge bg-info">{{ $ofsEnCours->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Numéro</th><th>BC</th><th>Démarrage</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($ofsEnCours as $of)
                                <tr>
                                    <td><strong>{{ $of->numero }}</strong></td>
                                    <td>#{{ $of->bc_id }}</td>
                                    <td>{{ optional($of->date_demarrage)->format('Y-m-d') ?? '—' }}</td>
                                    <td><a href="{{ route('menuiserie.production.show', ['slug' => request()->route('slug'), 'of' => $of->id]) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center">Aucun OF en fabrication.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <strong>Chantiers en retard</strong>
                    <span class="badge bg-danger">{{ $chantiersEnRetard->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Numéro</th><th>Statut</th><th>Fin prévue</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($chantiersEnRetard as $ch)
                                <tr class="table-warning">
                                    <td><strong>{{ $ch->numero }}</strong></td>
                                    <td><span class="badge bg-info">{{ $ch->statut }}</span></td>
                                    <td>{{ optional($ch->date_fin_prevue)->format('Y-m-d') }}</td>
                                    <td><a href="{{ route('menuiserie.chantiers.show', ['slug' => request()->route('slug'), 'chantier' => $ch->id]) }}" class="btn btn-sm btn-outline-danger">Ouvrir</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center">Aucun chantier en retard.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <strong>Stocks bas (sous seuil d'alerte)</strong>
                    <span class="badge bg-danger">{{ $stocksBas->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Code</th><th>Désignation</th><th class="text-end">Stock</th><th class="text-end">Seuil</th><th>Unité</th></tr></thead>
                        <tbody>
                            @forelse ($stocksBas as $s)
                                <tr class="table-danger">
                                    <td>{{ $s->matiere_code }}</td>
                                    <td>{{ $s->matiere_designation }}</td>
                                    <td class="text-end">{{ number_format((float) $s->quantite_actuelle, 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format((float) $s->seuil_alerte, 2, ',', ' ') }}</td>
                                    <td>{{ $s->unite }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center">Tous les stocks sont au-dessus du seuil.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-menuiserie360::layout>
