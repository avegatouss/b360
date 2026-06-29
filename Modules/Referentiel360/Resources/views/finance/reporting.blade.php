<x-referentiel360::layout title="Finance consolidée (Reporting 360°)">
    @php
        // Format lisible d'un montant string bcmath/décimal (2 décimales).
        $money = static fn (string $amount): string => number_format((float) $amount, 2, ',', ' ');
    @endphp

    <p class="text-muted">
        Vue lecture seule du registre financier miroir, consolidé par devise et par tiers.
        Les avoirs sont soustraits ; les documents annulés sont exclus.
    </p>

    {{-- ─── KPI : totaux par devise ──────────────────────────────────── --}}
    <h2 class="h5 mt-4 mb-3">Totaux par devise</h2>

    @if (empty($totalsByCurrency))
        <div class="alert alert-info">Aucun document financier consolidé pour cette instance.</div>
    @else
        <div class="row g-3">
            @foreach ($totalsByCurrency as $currency => $totals)
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase mb-3">{{ $currency }}</h6>
                            <dl class="row mb-0">
                                <dt class="col-7 fw-normal text-muted">CA (TTC)</dt>
                                <dd class="col-5 text-end fw-semibold">{{ $money($totals['ttc']) }}</dd>

                                <dt class="col-7 fw-normal text-muted">Encaissé</dt>
                                <dd class="col-5 text-end text-success">{{ $money($totals['paid']) }}</dd>

                                <dt class="col-7 fw-normal text-muted">Reste dû</dt>
                                <dd class="col-5 text-end text-danger">{{ $money($totals['due']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ─── CA par tiers (× devise) ──────────────────────────────────── --}}
    <h2 class="h5 mt-5 mb-3">CA par tiers</h2>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Tiers</th>
                        <th>Type</th>
                        <th class="text-center">Devise</th>
                        <th class="text-end">TTC</th>
                        <th class="text-end">Payé</th>
                        <th class="text-end">Dû</th>
                        <th class="text-end">Documents</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($byParty as $row)
                        <tr>
                            <td>{{ $row['display_name'] }}</td>
                            <td>
                                @if ($row['party_id'] === null)
                                    <span class="badge bg-secondary">—</span>
                                @else
                                    @if ($row['is_customer'])
                                        <span class="badge bg-primary">Client</span>
                                    @endif
                                    @if ($row['is_supplier'])
                                        <span class="badge bg-info">Fournisseur</span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">{{ $row['currency'] }}</td>
                            <td class="text-end">{{ $money($row['ttc']) }}</td>
                            <td class="text-end text-success">{{ $money($row['paid']) }}</td>
                            <td class="text-end text-danger">{{ $money($row['due']) }}</td>
                            <td class="text-end">{{ $row['nb_documents'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Aucune ligne.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3 mt-1">
        {{-- ─── Par module source ───────────────────────────────────── --}}
        <div class="col-md-6">
            <h2 class="h6 mt-4 mb-3">Par module source</h2>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th class="text-end">Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($byModule as $row)
                                <tr>
                                    <td>{{ $row['source_module'] }}</td>
                                    <td class="text-end">{{ $row['nb_documents'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Aucune donnée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ─── Par statut normalisé ────────────────────────────────── --}}
        <div class="col-md-6">
            <h2 class="h6 mt-4 mb-3">Par statut</h2>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Statut</th>
                                <th class="text-end">Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($byStatus as $row)
                                <tr>
                                    <td>{{ $row['status_normalized'] }}</td>
                                    <td class="text-end">{{ $row['nb_documents'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Aucune donnée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-referentiel360::layout>
