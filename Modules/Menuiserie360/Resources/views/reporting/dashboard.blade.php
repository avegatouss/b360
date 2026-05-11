<x-menuiserie360::layout title="Tableau de bord menuiserie">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Devis brouillons</h6>
                    <p class="display-6 mb-0">{{ $kpis['devis_brouillons'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Devis acceptés (30j)</h6>
                    <p class="display-6 mb-0">{{ $kpis['devis_acceptes_30j'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">OF en cours</h6>
                    <p class="display-6 mb-0">{{ $kpis['of_en_cours'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Chantiers en cours</h6>
                    <p class="display-6 mb-0">{{ $kpis['chantiers_en_cours'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">CA mois (factures émises)</h6>
                    <p class="h3 mb-0">{{ number_format($kpis['ca_mois'], 0, ',', ' ') }} XOF</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Créances clients</h6>
                    <p class="h3 mb-0">{{ number_format($kpis['creances'], 0, ',', ' ') }} XOF</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Encaissé (mois)</h6>
                    <p class="h3 mb-0 text-success">{{ number_format($kpis['encaisse_mois'], 0, ',', ' ') }} XOF</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Taux conversion devis (90j)</h6>
                    <p class="h3 mb-0">{{ number_format($kpis['taux_conversion_devis_90j'], 1, ',', ' ') }} %</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">CA mensuel (12 derniers mois, TTC)</div>
                <div class="card-body">
                    <canvas id="caMensuelChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-2">
                <div class="card-header">Top 5 clients (180j)</div>
                <ul class="list-group list-group-flush">
                    @forelse ($topClients as $c)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Client #{{ $c['client_id'] }} <small class="text-muted">({{ $c['count'] }} fac.)</small></span>
                            <strong>{{ number_format($c['ttc'], 0, ',', ' ') }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Aucune facture sur 180j.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card">
                <div class="card-header">Mix paiements (mois)</div>
                <div class="card-body">
                    @if (count($mixPaiements) > 0)
                        <canvas id="mixPaiementsChart" height="180"></canvas>
                        <ul class="list-group list-group-flush mt-3 small">
                            @foreach ($mixPaiements as $m)
                                <li class="list-group-item d-flex justify-content-between p-2">
                                    <span>{{ $m['method'] }}</span>
                                    <strong>{{ $m['share'] }}%</strong>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center mb-0">Aucun encaissement ce mois.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Exports & Journal</div>
        <div class="card-body">
            <form method="GET" action="{{ route('menuiserie.reporting.exports.comptable', ['slug' => request()->route('slug')]) }}" class="row g-2 align-items-end mb-2">
                <div class="col-md-3">
                    <label class="form-label small">Du</label>
                    <input type="date" name="from" value="{{ now()->startOfMonth()->toDateString() }}" class="form-control"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Au</label>
                    <input type="date" name="to" value="{{ now()->endOfMonth()->toDateString() }}" class="form-control"/>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary">Télécharger CSV comptable</button>
                </div>
            </form>
            <a href="{{ route('menuiserie.reporting.journal', ['slug' => request()->route('slug')]) }}" class="btn btn-outline-secondary">Voir le journal ventes & paiements</a>
            <a href="{{ route('menuiserie.reporting.operations', ['slug' => request()->route('slug')]) }}" class="btn btn-outline-info">Dashboard opérationnel</a>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CA mensuel — bar chart
            const caData = @json($caMensuel12m);
            const caCanvas = document.getElementById('caMensuelChart');
            if (caCanvas && caData.length > 0) {
                new Chart(caCanvas, {
                    type: 'bar',
                    data: {
                        labels: caData.map(r => r.mois),
                        datasets: [{
                            label: 'CA TTC (XOF)',
                            data: caData.map(r => r.ttc),
                            backgroundColor: 'rgba(13, 110, 253, 0.6)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => new Intl.NumberFormat('fr-FR').format(v),
                                },
                            },
                        },
                    },
                });
            }

            // Mix paiements — donut
            const mixData = @json($mixPaiements);
            const mixCanvas = document.getElementById('mixPaiementsChart');
            if (mixCanvas && mixData.length > 0) {
                new Chart(mixCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: mixData.map(m => m.method),
                        datasets: [{
                            data: mixData.map(m => m.share),
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.8)',
                                'rgba(13, 110, 253, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(220, 53, 69, 0.8)',
                                'rgba(108, 117, 125, 0.8)',
                                'rgba(102, 16, 242, 0.8)',
                            ],
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                    },
                });
            }
        });
    </script>
    @endpush
</x-menuiserie360::layout>
