<x-dashboard::layouts.master
    :title="'Rapport Canaux de distribution — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Rapport Canaux de distribution">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Rapport Canaux de distribution</h4>
            <h6>Synth&egrave;se des marges et r&eacute;partitions par canal</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ URL::asset('build/img/icons/pdf.svg') }}" alt="img"></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ URL::asset('build/img/icons/excel.svg') }}" alt="img"></a>
        </li>
    </ul>
</div>

{{-- Date Range Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('eshop360.reports.channels', $instance->slug ?? '') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Du</label>
                    <input type="date" name="from" class="form-control" value="{{ $from ?? now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Au</label>
                    <input type="date" name="to" class="form-control" value="{{ $to ?? now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filtrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Totals Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Marge totale</h6>
                <h3 class="fw-bold text-primary mb-0">{{ number_format($totals['total_margin'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Part dette totale</h6>
                <h3 class="fw-bold text-danger mb-0">{{ number_format($totals['total_debt'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Part canaux</h6>
                <h3 class="fw-bold text-success mb-0">{{ number_format($totals['total_channel'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Part propri&eacute;taire</h6>
                <h3 class="fw-bold text-info mb-0">{{ number_format($totals['total_owner'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
</div>

{{-- Per-Channel Breakdown Table --}}
<div class="card table-list-card">
    <div class="card-header">
        <h5 class="card-title mb-0">D&eacute;tail par canal</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>Canal</th>
                        <th>Marge totale</th>
                        <th>Part dette</th>
                        <th>Part canal</th>
                        <th>Part propri&eacute;taire</th>
                        <th>Nb transactions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td>
                            <a href="{{ route('eshop360.channels.show', [$instance->slug ?? '', $row['channel']]) }}" class="fw-semibold">{{ $row['channel']->name }}</a>
                        </td>
                        <td class="fw-bold">{{ number_format($row['summary']['total_margin'] ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($row['summary']['total_debt'] ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($row['summary']['total_channel'] ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($row['summary']['total_owner'] ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ $row['summary']['transactions_count'] ?? $row['summary']['count'] ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Aucune donn&eacute;e pour cette p&eacute;riode.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
