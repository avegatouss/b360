<x-dashboard::layouts.master
    :title="__('CODIFARM') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('CODIFARM')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Rapport CODIFARM') }}</h4>
                        <h6>{{ __('Resume des marges CODIFARM') }}</h6>
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

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">{{ __('CA Total') }}</h5>
                            <h4 class="fw-bold">{{ number_format($data['total_revenue'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">{{ __('Cout total') }}</h5>
                            <h4 class="fw-bold">{{ number_format($data['total_cost'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">{{ __('Marge brute') }}</h5>
                            <h4 class="fw-bold text-success">{{ number_format($data['gross_margin'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">{{ __('Taux de marge') }}</h5>
                            <h4 class="fw-bold">{{ number_format($data['margin_rate'] ?? 0, 1) }}%</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Produit') }}</th>
                                    <th>{{ __('Code CODIFARM') }}</th>
                                    <th>{{ __('Qte vendue') }}</th>
                                    <th>{{ __('Prix vente') }}</th>
                                    <th>{{ __('Prix achat') }}</th>
                                    <th>{{ __('Marge unitaire') }}</th>
                                    <th>{{ __('Marge totale') }}</th>
                                    <th>{{ __('Taux') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['products'] ?? $data['items'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['product'] ?? $row['name'] ?? '---' }}</td>
                                    <td><code>{{ $row['codifarm'] ?? $row['code'] ?? '---' }}</code></td>
                                    <td>{{ $row['quantity'] ?? 0 }}</td>
                                    <td>{{ number_format($row['selling_price'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['cost_price'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['unit_margin'] ?? 0, 2) }}</td>
                                    <td class="fw-bold text-success">{{ number_format($row['total_margin'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['margin_rate'] ?? 0, 1) }}%</td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted">{{ __('Aucune donnee') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
