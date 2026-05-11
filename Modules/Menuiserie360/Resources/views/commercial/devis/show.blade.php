<x-menuiserie360::layout title="Devis {{ $devis->numero }}">
    <x-menuiserie360::page-header
        :title="'Devis ' . $devis->numero"
        :back-route="route('menuiserie.devis.index', ['slug' => request()->route('slug')])"
        back-label="Liste des devis"
        :status="$devis->statut"
        :status-variant="$devis->statut === 'accepte' ? 'success' : ($devis->statut === 'refuse' ? 'danger' : 'secondary')"
    >
        <x-slot:actions>
            <a href="{{ route('menuiserie.devis.pdf', ['slug' => request()->route('slug'), 'devis' => $devis->id]) }}" class="btn btn-outline-secondary btn-sm">Télécharger PDF</a>
        </x-slot:actions>
    </x-menuiserie360::page-header>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <p><strong>Client :</strong> #{{ $devis->client_id }}</p>
                    <p><strong>Montant HT :</strong> {{ number_format((float) $devis->montant_ht, 0, ',', ' ') }} XOF</p>
                    <p><strong>TVA ({{ (float) $devis->taux_tva * 100 }}%) :</strong> {{ number_format((float) $devis->montant_tva, 0, ',', ' ') }} XOF</p>
                    <p><strong>Montant TTC :</strong> {{ number_format((float) $devis->montant_ttc, 0, ',', ' ') }} XOF</p>

                    <h5 class="mt-4">Lignes</h5>
                    <table class="table table-sm">
                        <thead><tr><th>Désignation</th><th>Dimensions</th><th>Qté</th><th class="text-end">PU HT</th><th class="text-end">Total HT</th></tr></thead>
                        <tbody>
                            @foreach ($devis->lignes as $ligne)
                                <tr>
                                    <td>{{ $ligne->designation }}</td>
                                    <td>{{ $ligne->largeur_mm ?? '-' }} × {{ $ligne->hauteur_mm ?? '-' }} mm</td>
                                    <td>{{ $ligne->quantite }}</td>
                                    <td class="text-end">{{ number_format((float) $ligne->prix_unitaire_ht, 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format((float) $ligne->montant_ht, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('menuiserie.devis.pdf', ['slug' => request()->route('slug'), 'devis' => $devis->id]) }}" class="btn btn-outline-secondary w-100 mb-2">Télécharger PDF</a>
                    @if (in_array($devis->statut, ['brouillon', 'soumis', 'valide']))
                        <form method="POST" action="{{ route('menuiserie.devis.accepter', ['slug' => request()->route('slug'), 'devis' => $devis->id]) }}">
                            @csrf
                            <input type="number" name="acompte_pct" value="30" min="0" max="100" step="1" class="form-control mb-2"/>
                            <button class="btn btn-success w-100">Accepter & créer BC</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-menuiserie360::layout>
