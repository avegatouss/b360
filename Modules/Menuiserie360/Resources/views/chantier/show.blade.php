<x-menuiserie360::layout title="Chantier {{ $chantier->numero }}">
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Statut :</strong> <span class="badge bg-info">{{ $chantier->statut }}</span></p>
            <p><strong>BC :</strong> #{{ $chantier->bc_id }} | <strong>Client :</strong> #{{ $chantier->client_id }}</p>
            <p><strong>Adresse pose :</strong> {{ $chantier->adresse_pose ?? '—' }}</p>
            <p><strong>Contact :</strong> {{ $chantier->contact_chantier ?? '—' }}</p>
            <p><strong>Chef chantier :</strong> #{{ $chantier->chef_chantier_id ?? '—' }}</p>
            <p><strong>Période prévue :</strong> {{ optional($chantier->date_debut_prevue)->format('Y-m-d') ?? '—' }} → {{ optional($chantier->date_fin_prevue)->format('Y-m-d') ?? '—' }}</p>

            @if ($chantier->statut !== 'termine' && $chantier->statut !== 'livre' && $chantier->statut !== 'annule')
                <form method="POST" action="{{ route('menuiserie.chantiers.terminer', ['slug' => request()->route('slug'), 'chantier' => $chantier->id]) }}" class="mt-3">
                    @csrf
                    <button class="btn btn-success" onclick="return confirm('Clôturer ce chantier ? La facture de solde sera générée automatiquement.')">Clôturer le chantier (générer facture solde)</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Photos avancement ({{ $chantier->getMedia('avancement')->count() }})</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('menuiserie.chantiers.photos.upload', ['slug' => request()->route('slug'), 'chantier' => $chantier->id]) }}" enctype="multipart/form-data" class="row g-2 mb-3 align-items-end">
                @csrf
                <div class="col-md-6">
                    <label class="form-label small">Photo (JPG/PNG/WebP, max 8 Mo)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="form-control"/>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Légende</label>
                    <input type="text" name="legende" maxlength="200" class="form-control" placeholder="Ex : pose fenêtre cuisine"/>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Téléverser</button>
                </div>
            </form>
            <div class="row g-2">
                @forelse ($chantier->getMedia('avancement') as $photo)
                    <div class="col-md-3">
                        <div class="card">
                            <img src="{{ $photo->getUrl() }}" alt="{{ $photo->name }}" class="card-img-top" style="object-fit:cover;height:160px;"/>
                            <div class="card-body p-2">
                                <small class="d-block text-muted">{{ $photo->getCustomProperty('legende') ?: $photo->name }}</small>
                                <small class="text-muted">{{ $photo->created_at->format('Y-m-d H:i') }}</small>
                                <form method="POST" action="{{ route('menuiserie.chantiers.photos.delete', ['slug' => request()->route('slug'), 'chantier' => $chantier->id, 'media' => $photo->id]) }}" class="mt-1">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Supprimer cette photo ?')">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small col-12">Aucune photo. Téléversez la première photo d'avancement.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Étapes ({{ $chantier->etapes->count() }})</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Ordre</th><th>Étape</th><th>Avancement</th><th>Statut</th><th>Mise à jour</th></tr></thead>
                <tbody>
                    @foreach ($chantier->etapes as $e)
                        <tr>
                            <td>{{ $e->ordre }}</td>
                            <td>{{ $e->nom }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $e->avancement_pct }}%">{{ $e->avancement_pct }}%</div>
                                </div>
                                <form method="POST" action="{{ route('menuiserie.chantiers.avancer', ['slug' => request()->route('slug'), 'chantier' => $chantier->id]) }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="etape_id" value="{{ $e->id }}"/>
                                    <input type="number" name="avancement_pct" min="0" max="100" value="{{ $e->avancement_pct }}" style="width: 80px;" class="form-control form-control-sm d-inline"/>
                                    <button class="btn btn-sm btn-primary">Mettre à jour</button>
                                </form>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $e->statut }}</span></td>
                            <td>{{ optional($e->updated_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-menuiserie360::layout>
