<x-menuiserie360::layout title="Tableau de bord menuiserie">
    @php($slug = request()->route('slug'))

    {{-- V1.2-1 : barre de filtre période + drilldown KPIs --}}
    <form method="GET" class="card mb-3" x-data="{ preset: '{{ $period['preset'] }}' }">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Période</label>
                    <select name="preset" x-model="preset" class="form-select form-select-sm" onchange="if (preset !== 'custom') this.form.submit()">
                        <option value="this-month">Mois en cours</option>
                        <option value="last-month">Mois dernier</option>
                        <option value="last-3m">3 derniers mois</option>
                        <option value="last-6m">6 derniers mois</option>
                        <option value="last-12m">12 derniers mois</option>
                        <option value="ytd">Depuis le 1er janvier</option>
                        <option value="custom">Personnalisé</option>
                    </select>
                </div>
                <div class="col-md-3" x-show="preset === 'custom'" x-cloak>
                    <label class="form-label small mb-1">Du</label>
                    <input type="date" name="from" value="{{ $period['from']->toDateString() }}" class="form-control form-control-sm"/>
                </div>
                <div class="col-md-3" x-show="preset === 'custom'" x-cloak>
                    <label class="form-label small mb-1">Au</label>
                    <input type="date" name="to" value="{{ $period['to']->toDateString() }}" class="form-control form-control-sm"/>
                </div>
                <div class="col-md-auto" x-show="preset === 'custom'" x-cloak>
                    <button type="submit" class="btn btn-sm btn-primary">Appliquer</button>
                </div>
                <div class="col-md-auto ms-auto text-end">
                    <small class="text-muted">
                        <strong>{{ $period['label'] }}</strong> ·
                        {{ $period['from']->format('Y-m-d') }} → {{ $period['to']->format('Y-m-d') }}
                    </small>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('menuiserie.devis.index', ['slug' => $slug, 'statut' => 'brouillon']) }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h6 class="text-muted">Devis brouillons</h6>
                    <p class="display-6 mb-0">{{ $kpis['devis_brouillons'] }}</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('menuiserie.devis.index', ['slug' => $slug, 'statut' => 'accepte']) }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h6 class="text-muted">Devis acceptés ({{ $period['label'] }})</h6>
                    <p class="display-6 mb-0">{{ $kpis['devis_acceptes_periode'] }}</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('menuiserie.production.index', ['slug' => $slug, 'statut' => 'en_cours']) }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h6 class="text-muted">OF en cours</h6>
                    <p class="display-6 mb-0">{{ $kpis['of_en_cours'] }}</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('menuiserie.chantiers.index', ['slug' => $slug, 'statut' => 'en_cours']) }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h6 class="text-muted">Chantiers en cours</h6>
                    <p class="display-6 mb-0">{{ $kpis['chantiers_en_cours'] }}</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('menuiserie.reporting.journal', ['slug' => $slug, 'from' => $period['from']->toDateString(), 'to' => $period['to']->toDateString()]) }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h6 class="text-muted">CA {{ $period['label'] }}</h6>
                    <p class="h3 mb-0">{{ number_format($kpis['ca_periode'], 0, ',', ' ') }} XOF</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('menuiserie.factures.index', ['slug' => $slug, 'statut' => 'issued']) }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h6 class="text-muted">Créances clients</h6>
                    <p class="h3 mb-0">{{ number_format($kpis['creances'], 0, ',', ' ') }} XOF</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('menuiserie.reporting.journal', ['slug' => $slug, 'from' => $period['from']->toDateString(), 'to' => $period['to']->toDateString()]) }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h6 class="text-muted">Encaissé ({{ $period['label'] }})</h6>
                    <p class="h3 mb-0 text-success">{{ number_format($kpis['encaisse_periode'], 0, ',', ' ') }} XOF</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">Taux conversion devis ({{ $period['label'] }})</h6>
                    <p class="h3 mb-0">{{ number_format($kpis['taux_conversion_devis_periode'], 1, ',', ' ') }} %</p>
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
                <div class="card-header">Top 5 clients ({{ $period['label'] }})</div>
                <ul class="list-group list-group-flush">
                    @forelse ($topClients as $c)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Client #{{ $c['client_id'] }} <small class="text-muted">({{ $c['count'] }} fac.)</small></span>
                            <strong>{{ number_format($c['ttc'], 0, ',', ' ') }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Aucune facture sur la période.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card">
                <div class="card-header">Mix paiements ({{ $period['label'] }})</div>
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
            <a href="{{ route('menuiserie.reporting.exports.dashboard-pdf', ['slug' => request()->route('slug'), 'preset' => $period['preset'], 'from' => $period['from']->toDateString(), 'to' => $period['to']->toDateString()]) }}" class="btn btn-outline-danger">
                <i class="ti ti-file-type-pdf"></i> Export PDF du dashboard (période)
            </a>
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
