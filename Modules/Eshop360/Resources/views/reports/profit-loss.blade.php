@php $slug = $instance->slug ?? ''; @endphp
<x-dashboard::layouts.master
    :title="__('Compte de resultat') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Compte de resultat')">

    <div class="page-wrapper">
        <div class="content">
            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Compte de resultat') }}</h4>
                        <h6>{{ __('Profit et pertes sur la periode') }}</h6>
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
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Au') }}</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
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

            @php
                $prev = $data['prev'] ?? null;
                $netProfit = $data['net_profit'] ?? 0;
                $grossProfit = $data['gross_profit'] ?? 0;
                $grossMargin = $data['gross_margin'] ?? 0;
                $cogs = $data['cogs'] ?? 0;
                $totalRevenue = $data['total_revenue'] ?? 0;
                $totalExpenses = $data['total_expenses'] ?? 0;
                $netColor = ($netProfit >= 0) ? 'success' : 'danger';
                $revenueWidth = ($totalRevenue + $totalExpenses) > 0 ? ($totalRevenue / ($totalRevenue + $totalExpenses)) * 100 : 50;
            @endphp

            {{-- Visual Balance Bar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold text-success"><i class="ti ti-arrow-up me-1"></i>{{ __('Revenus') }}: {{ number_format($totalRevenue, 0, ',', ' ') }}</span>
                        <span class="fw-semibold text-danger"><i class="ti ti-arrow-down me-1"></i>{{ __('Depenses') }}: {{ number_format($totalExpenses + $cogs, 0, ',', ' ') }}</span>
                    </div>
                    <div class="progress" style="height: 24px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $revenueWidth }}%">
                            {{ __('Revenus') }}
                        </div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ 100 - $revenueWidth }}%">
                            {{ __('Depenses') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- P&L Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 shadow-sm border-start border-success border-3">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">{{ __('Chiffre d\'affaires') }}</p>
                            <h4 class="fw-bold text-success">{{ number_format($totalRevenue, 0, ',', ' ') }}</h4>
                            @if($prev)
                                <small class="text-muted">{{ __('Prec') }}: {{ number_format($prev['total_revenue'] ?? 0, 0, ',', ' ') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 shadow-sm border-start border-warning border-3">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">{{ __('Cout des ventes (COGS)') }}</p>
                            <h4 class="fw-bold text-warning">{{ number_format($cogs, 0, ',', ' ') }}</h4>
                            @if($prev)
                                <small class="text-muted">{{ __('Prec') }}: {{ number_format($prev['cogs'] ?? 0, 0, ',', ' ') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 shadow-sm border-start border-info border-3">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">{{ __('Marge brute') }}</p>
                            <h4 class="fw-bold text-info">{{ number_format($grossProfit, 0, ',', ' ') }}</h4>
                            <span class="badge bg-info bg-opacity-25 text-info">{{ $grossMargin }}%</span>
                            @if($prev)
                                <br><small class="text-muted">{{ __('Prec') }}: {{ number_format($prev['gross_profit'] ?? 0, 0, ',', ' ') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 shadow-sm border-start border-danger border-3">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">{{ __('Charges') }}</p>
                            <h4 class="fw-bold text-danger">{{ number_format($totalExpenses, 0, ',', ' ') }}</h4>
                            @if($prev)
                                <small class="text-muted">{{ __('Prec') }}: {{ number_format($prev['total_expenses'] ?? 0, 0, ',', ' ') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- NET PROFIT Big Card --}}
            @php $netDiff = $prev ? ($netProfit - ($prev['net_profit'] ?? 0)) : null; @endphp
            <div class="card border-0 shadow-sm mb-4 bg-{{ $netColor }}-transparent">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted mb-2"><i class="ti ti-report-money me-2"></i>{{ __('RESULTAT NET') }}</h5>
                    <h1 class="display-4 fw-bold text-{{ $netColor }}">{{ number_format($netProfit, 0, ',', ' ') }}</h1>
                    @if($netDiff !== null)
                        <p class="mb-0">
                            <span class="{{ $netDiff >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="ti ti-trending-{{ $netDiff >= 0 ? 'up' : 'down' }} me-1"></i>
                                {{ $netDiff >= 0 ? '+' : '' }}{{ number_format($netDiff, 0, ',', ' ') }} {{ __('vs periode precedente') }}
                            </span>
                        </p>
                    @endif
                </div>
            </div>

            {{-- Revenue & Expense Breakdown --}}
            <div class="row g-3">
                {{-- Revenue Section --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0 text-success"><i class="ti ti-arrow-up-circle me-2"></i>{{ __('Revenus') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <tbody>
                                        @forelse($data['revenue_lines'] ?? [] as $line)
                                        <tr>
                                            <td>{{ $line['label'] ?? '---' }}</td>
                                            <td class="text-end text-success fw-semibold">{{ number_format($line['amount'] ?? 0, 0, ',', ' ') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="2" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr class="fw-bold">
                                            <td>{{ __('Total revenus') }}</td>
                                            <td class="text-end text-success">{{ number_format($totalRevenue, 0, ',', ' ') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Expenses Section --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0 text-danger"><i class="ti ti-arrow-down-circle me-2"></i>{{ __('Depenses') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <tbody>
                                        @forelse($data['expense_lines'] ?? [] as $line)
                                        <tr>
                                            <td>{{ $line['label'] ?? '---' }}</td>
                                            <td class="text-end text-danger fw-semibold">{{ number_format($line['amount'] ?? 0, 0, ',', ' ') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="2" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr class="fw-bold">
                                            <td>{{ __('Total depenses') }}</td>
                                            <td class="text-end text-danger">{{ number_format($totalExpenses, 0, ',', ' ') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charges imputees --}}
            @if(!empty($chargesData))
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="ti ti-file-invoice me-2"></i>{{ __('Charges imputees') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-end">{{ __('Montant') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chargesData['by_type'] ?? [] as $type => $amount)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                                    <td class="text-end text-danger">{{ number_format($amount, 0, ',', ' ') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

</x-dashboard::layouts.master>
