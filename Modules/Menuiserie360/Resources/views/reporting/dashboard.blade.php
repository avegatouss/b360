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
</x-menuiserie360::layout>
