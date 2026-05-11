<x-menuiserie360::layout title="Chantier {{ $chantier->numero }}">
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Statut :</strong> <span class="badge bg-info">{{ $chantier->statut }}</span></p>
            <p><strong>BC :</strong> #{{ $chantier->bc_id }} | <strong>Client :</strong> #{{ $chantier->client_id }}</p>
            <p><strong>Adresse pose :</strong> {{ $chantier->adresse_pose ?? '—' }}</p>
            <p><strong>Contact :</strong> {{ $chantier->contact_chantier ?? '—' }}</p>
            <p><strong>Chef chantier :</strong> #{{ $chantier->chef_chantier_id ?? '—' }}</p>
            <p><strong>Période prévue :</strong> {{ optional($chantier->date_debut_prevue)->format('Y-m-d') ?? '—' }} → {{ optional($chantier->date_fin_prevue)->format('Y-m-d') ?? '—' }}</p>
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
