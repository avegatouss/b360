@php $slug = $instance->slug ?? ''; @endphp
<x-dashboard::layouts.master
    :title="__('Rapport des ventes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Rapport des ventes')">

    <div class="page-wrapper">
        <div class="content">
            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Rapport des ventes') }}</h4>
                        <h6>{{ __('Analyse detaillee des ventes par periode') }}</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="{{ __('Exporter') }}">
                        <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
                    </a>
                </div>
            </div>

            {{-- Filter Card --}}
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body py-3">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Du') }}</label>
                            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Au') }}</label>
                            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-refresh me-1"></i>{{ __('Reinitialiser') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-success border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-success-transparent rounded-circle">
                                    <i class="ti ti-cash fs-20 text-success"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Chiffre d\'affaires') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($totals['revenue'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-primary border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="ti ti-shopping-cart fs-20 text-primary"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Commandes') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($totals['orders'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-warning border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-warning-transparent rounded-circle">
                                    <i class="ti ti-receipt-tax fs-20 text-warning"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Taxes') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($totals['tax'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-info border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-info-transparent rounded-circle">
                                    <i class="ti ti-discount fs-20 text-info"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Remises') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($totals['discount'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-danger border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-danger-transparent rounded-circle">
                                    <i class="ti ti-alert-circle fs-20 text-danger"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Impayes') }}</p>
                                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($totals['due'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daily Sales Chart --}}
            @if($salesByDay->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="ti ti-chart-bar me-2"></i>{{ __('Ventes journalieres') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailySalesChart" height="300"></canvas>
                </div>
            </div>
            @endif

            {{-- Tables --}}
            <div class="row g-3 mb-4">
                {{-- Sales by Day --}}
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-calendar me-2"></i>{{ __('Ventes par jour') }}
                                <span class="badge bg-primary ms-2">{{ $salesByDay->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th class="text-end">{{ __('Commandes') }}</th>
                                            <th class="text-end">{{ __('Total') }}</th>
                                            <th class="text-end">{{ __('Taxes') }}</th>
                                            <th class="text-end">{{ __('Remises') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($salesByDay as $day)
                                        <tr>
                                            <td><i class="ti ti-calendar-event me-1 text-muted"></i>{{ $day->date }}</td>
                                            <td class="text-end">{{ number_format($day->order_count, 0, ',', ' ') }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($day->total, 0, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($day->tax, 0, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($day->discount, 0, ',', ' ') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                {{-- Sales by Source --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-world me-2"></i>{{ __('Ventes par source') }}
                                <span class="badge bg-primary ms-2">{{ $salesBySource->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Source') }}</th>
                                            <th class="text-end">{{ __('Commandes') }}</th>
                                            <th class="text-end">{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($salesBySource as $source)
                                        <tr>
                                            <td><span class="badge bg-light text-dark">{{ ucfirst($source->source ?? __('Non defini')) }}</span></td>
                                            <td class="text-end">{{ number_format($source->count, 0, ',', ' ') }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($source->total, 0, ',', ' ') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sales by Payment Method --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-credit-card me-2"></i>{{ __('Ventes par mode de paiement') }}
                                <span class="badge bg-primary ms-2">{{ $salesByPayment->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Mode de paiement') }}</th>
                                            <th class="text-end">{{ __('Commandes') }}</th>
                                            <th class="text-end">{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($salesByPayment as $payment)
                                        <tr>
                                            <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? __('Non defini'))) }}</span></td>
                                            <td class="text-end">{{ number_format($payment->count, 0, ',', ' ') }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($payment->total, 0, ',', ' ') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($salesByDay) && $salesByDay->isNotEmpty())
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('dailySalesChart').getContext('2d');
            const data = @json($salesByDay);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [
                        {
                            label: '{{ __("CA") }}',
                            data: data.map(d => d.total),
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            order: 2
                        },
                        {
                            label: '{{ __("Commandes") }}',
                            data: data.map(d => d.order_count),
                            type: 'line',
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 2,
                            pointRadius: 4,
                            fill: false,
                            yAxisID: 'y1',
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    scales: {
                        y: { beginAtZero: true, position: 'left', ticks: { callback: v => new Intl.NumberFormat('fr-FR').format(v) } },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
    @endpush
    @endif

</x-dashboard::layouts.master>
