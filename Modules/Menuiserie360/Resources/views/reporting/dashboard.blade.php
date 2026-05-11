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
        </div>
    </div>
</x-menuiserie360::layout>
