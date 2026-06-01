@php
    $slug = $instance->slug ?? '';
    $currency = function_exists('currency') ? currency($instance->id ?? null) : ($eshopCurrency ?? 'XAF');
    $money = fn ($amount) => number_format((float) $amount, 2, '.', '');
@endphp
<x-dashboard::layouts.master
    :title="__('Vue d\'ensemble') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Vue d\'ensemble')">


            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Vue d\'ensemble') }}</h4>
                        <h6>{{ __('Synthese globale de l\'activite') }}</h6>
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
                            <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Au') }}</label>
                            <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control form-control-sm">
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

            {{-- KPI Row 1 --}}
            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-success border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-md bg-success-transparent rounded-circle">
                                        <i class="ti ti-cash fs-20 text-success"></i>
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Ventes totales') }}</p>
                                    <h4 class="fw-bold mb-0">{{ $money($data['total_sales'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-info border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-md bg-info-transparent rounded-circle">
                                        <i class="ti ti-truck fs-20 text-info"></i>
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Achats totaux') }}</p>
                                    <h4 class="fw-bold mb-0">{{ $money($data['total_purchases'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-danger border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-md bg-danger-transparent rounded-circle">
                                        <i class="ti ti-receipt fs-20 text-danger"></i>
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Depenses') }}</p>
                                    <h4 class="fw-bold mb-0">{{ $money($data['total_expenses'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    @php $netProfit = $data['net_profit'] ?? 0; @endphp
                    <div class="card border-0 shadow-sm border-start border-{{ $netProfit >= 0 ? 'success' : 'danger' }} border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-md bg-{{ $netProfit >= 0 ? 'success' : 'danger' }}-transparent rounded-circle">
                                        <i class="ti ti-trending-{{ $netProfit >= 0 ? 'up' : 'down' }} fs-20 text-{{ $netProfit >= 0 ? 'success' : 'danger' }}"></i>
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Benefice net') }}</p>
                                    <h4 class="fw-bold mb-0 text-{{ $netProfit >= 0 ? 'success' : 'danger' }}">{{ $money($netProfit) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI Row 2 --}}
            <div class="row g-3 mb-4">
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-primary border-3 h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1 small">{{ __('Commandes') }}</p>
                            <h4 class="fw-bold mb-0">{{ number_format($data['total_orders'] ?? 0, 0, ',', ' ') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-secondary border-3 h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1 small">{{ __('Clients') }}</p>
                            <h4 class="fw-bold mb-0">{{ number_format($data['total_customers'] ?? 0, 0, ',', ' ') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-warning border-3 h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1 small">{{ __('Produits') }}</p>
                            <h4 class="fw-bold mb-0">{{ number_format($data['total_products'] ?? 0, 0, ',', ' ') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-success border-3 h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1 small">{{ __('CA encaisse') }}</p>
                            <h4 class="fw-bold mb-0">{{ $money($data['revenue_collected'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-danger border-3 h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1 small">{{ __('Impayes') }}</p>
                            <h4 class="fw-bold mb-0 text-danger">{{ $money($data['outstanding_dues'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daily Revenue Chart --}}
            @if(!empty($data['daily_revenue']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="ti ti-chart-bar me-2"></i>{{ __('Chiffre d\'affaires journalier') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyRevenueChart" height="300"></canvas>
                </div>
            </div>
            @endif

            {{-- Tables: Top Products & Top Customers --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-star me-2"></i>{{ __('Top 5 produits') }}
                                @if(!empty($data['top_products']))
                                    <span class="badge bg-primary ms-2">{{ count($data['top_products']) }}</span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Produit') }}</th>
                                            <th class="text-end">{{ __('Qte vendue') }}</th>
                                            <th class="text-end">{{ __('CA') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data['top_products'] ?? [] as $i => $product)
                                        <tr>
                                            <td><span class="badge bg-secondary">{{ $i + 1 }}</span></td>
                                            <td>{{ $product['name'] ?? '---' }}</td>
                                            <td class="text-end">{{ number_format($product['quantity'] ?? 0, 0, ',', ' ') }}</td>
                                            <td class="text-end fw-semibold">{{ $money($product['revenue'] ?? 0) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-users me-2"></i>{{ __('Top 5 clients') }}
                                @if(!empty($data['top_customers']))
                                    <span class="badge bg-primary ms-2">{{ count($data['top_customers']) }}</span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Client') }}</th>
                                            <th class="text-end">{{ __('Commandes') }}</th>
                                            <th class="text-end">{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data['top_customers'] ?? [] as $i => $customer)
                                        <tr>
                                            <td><span class="badge bg-secondary">{{ $i + 1 }}</span></td>
                                            <td>{{ $customer['name'] ?? '---' }}</td>
                                            <td class="text-end">{{ number_format($customer['orders'] ?? 0, 0, ',', ' ') }}</td>
                                            <td class="text-end fw-semibold">{{ $money($customer['total'] ?? 0) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
     

    @if(!empty($data['daily_revenue']))
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('dailyRevenueChart').getContext('2d');
            const dailyData = @json($data['daily_revenue']);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dailyData.map(d => d.date),
                    datasets: [{
                        label: '{{ __("CA journalier") }}',
                        data: dailyData.map(d => d.total),
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('fr-FR').format(v) } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
    @endpush
    @endif

</x-dashboard::layouts.master>
