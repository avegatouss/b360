<x-menuiserie360::layout title="OF {{ $of->numero }}">
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Statut :</strong> <span class="badge bg-info">{{ $of->statut }}</span></p>
            <p><strong>BC :</strong> #{{ $of->bc_id }}</p>
            <p><strong>Démarrage :</strong> {{ optional($of->date_demarrage)->format('Y-m-d H:i') ?? '—' }}</p>
            <p><strong>Fin :</strong> {{ optional($of->date_fin_reelle)->format('Y-m-d H:i') ?? '—' }}</p>

            <div class="d-flex gap-2 mt-3">
                @if ($of->statut === 'en_attente')
                    <form method="POST" action="{{ route('menuiserie.production.lancer', ['slug' => request()->route('slug'), 'of' => $of->id]) }}">
                        @csrf <button class="btn btn-primary">Lancer en production</button>
                    </form>
                @endif
                @if ($of->statut === 'en_cours')
                    <form method="POST" action="{{ route('menuiserie.production.terminer', ['slug' => request()->route('slug'), 'of' => $of->id]) }}">
                        @csrf <button class="btn btn-success">Marquer terminé</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">Lignes à fabriquer ({{ $of->lignes->count() }})</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Désignation</th><th>Dimensions</th><th>Qté</th><th>Statut</th></tr></thead>
                        <tbody>
                            @foreach ($of->lignes as $l)
                                <tr>
                                    <td>{{ $l->designation }}</td>
                                    <td>{{ $l->largeur_mm ?? '-' }} × {{ $l->hauteur_mm ?? '-' }} mm</td>
                                    <td>{{ $l->quantite }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $l->statut_ligne }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Besoin matières (auto)</div>
                <div class="card-body">
                    @if (count($disponibilite) === 0)
                        <p class="text-muted small">Aucune matière requise calculée. Le calcul automatique nécessite que les TypeProduitMenuiserie correspondants soient configurés (matieres_principales JSON) — cf. P2-2.</p>
                    @else
                        <table class="table table-sm">
                            <thead><tr><th>Matière #</th><th>Requis</th><th>Stock OK ?</th></tr></thead>
                            <tbody>
                                @foreach ($disponibilite as $d)
                                    <tr>
                                        <td>#{{ $d['matiere_id'] }}</td>
                                        <td>{{ $d['requise'] }}</td>
                                        <td>
                                            @if ($d['disponible'])
                                                <span class="badge bg-success">OK</span>
                                            @else
                                                <span class="badge bg-danger">Insuffisant</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-menuiserie360::layout>
