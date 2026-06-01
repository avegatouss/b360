@php $slug = $instance->slug ?? ''; @endphp
<x-dashboard::layouts.master
    :title="__('Rapport facturation') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Rapport facturation')">

    <div class="page-wrapper">
        <div class="content">
            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Rapport facturation') }}</h4>
                        <h6>{{ __('Analyse des factures emises') }}</h6>
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
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-primary border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="ti ti-file-invoice fs-20 text-primary"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Total facture') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($summary['total_invoiced'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-success border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-success-transparent rounded-circle">
                                    <i class="ti ti-circle-check fs-20 text-success"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Paye') }}</p>
                                    <h4 class="fw-bold mb-0 text-success">{{ number_format($summary['total_paid'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-warning border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-warning-transparent rounded-circle">
                                    <i class="ti ti-clock fs-20 text-warning"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Impaye') }}</p>
                                    <h4 class="fw-bold mb-0 text-warning">{{ number_format($summary['total_due'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-danger border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-danger-transparent rounded-circle">
                                    <i class="ti ti-alert-circle fs-20 text-danger"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('En retard') }}</p>
                                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($summary['overdue_count'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tables --}}
            <div class="row g-3">
                {{-- Invoices by Status --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-list-check me-2"></i>{{ __('Par statut') }}
                                <span class="badge bg-primary ms-2">{{ $invoicesByStatus->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Statut') }}</th>
                                            <th class="text-end">{{ __('Nombre') }}</th>
                                            <th class="text-end">{{ __('Montant') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoicesByStatus as $row)
                                        @php
                                            $statusColors = [
                                                'paid' => 'success',
                                                'partial' => 'warning',
                                                'unpaid' => 'danger',
                                                'overdue' => 'danger',
                                                'draft' => 'secondary',
                                                'cancelled' => 'dark',
                                            ];
                                            $color = $statusColors[$row->status] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $color }}-transparent text-{{ $color }}">
                                                    <i class="ti ti-point-filled me-1"></i>{{ ucfirst($row->status) }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ number_format($row->count, 0, ',', ' ') }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($row->total, 0, ',', ' ') }}</td>
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

                {{-- Invoices by Month --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-calendar-stats me-2"></i>{{ __('Par mois') }}
                                <span class="badge bg-primary ms-2">{{ $invoicesByMonth->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Periode') }}</th>
                                            <th class="text-end">{{ __('Nombre') }}</th>
                                            <th class="text-end">{{ __('Total') }}</th>
                                            <th class="text-end">{{ __('Paye') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $months = [1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Aou', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
                                        @endphp
                                        @forelse($invoicesByMonth as $row)
                                        <tr>
                                            <td><i class="ti ti-calendar me-1 text-muted"></i>{{ $months[$row->month] ?? $row->month }} {{ $row->year }}</td>
                                            <td class="text-end">{{ number_format($row->count, 0, ',', ' ') }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($row->total, 0, ',', ' ') }}</td>
                                            <td class="text-end text-success">{{ number_format($row->paid, 0, ',', ' ') }}</td>
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

            {{-- Monthly Chart --}}
            @if($invoicesByMonth->isNotEmpty())
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="ti ti-chart-bar me-2"></i>{{ __('Evolution mensuelle') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="invoicesChart" height="300"></canvas>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if(isset($invoicesByMonth) && $invoicesByMonth->isNotEmpty())
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('invoicesChart').getContext('2d');
            const months = ['', 'Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'];
            const data = @json($invoicesByMonth);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => months[d.month] + ' ' + d.year),
                    datasets: [
                        {
                            label: '{{ __("Total") }}',
                            data: data.map(d => d.total),
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: '{{ __("Paye") }}',
                            data: data.map(d => d.paid),
                            backgroundColor: 'rgba(16, 185, 129, 0.6)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
