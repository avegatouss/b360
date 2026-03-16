<x-dashboard::layouts.master
    :title="'Profits & Pertes — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Profits & Pertes">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Profits & Pertes</h4>
                        <h6>Bilan des revenus et depenses</h6>
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

            {{-- Date filter --}}
            <form method="GET" class="row g-2 mb-4 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Du</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">Au</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
            </form>

            {{-- Summary Cards —— Revenue / COGS / Gross / Expenses / Net --}}
            @php
                $prev = $data['prev'] ?? null;
                $netProfit = $data['net_profit'] ?? 0;
                $grossProfit = $data['gross_profit'] ?? 0;
                $grossMargin = $data['gross_margin'] ?? 0;
                $cogs = $data['cogs'] ?? 0;

                $netDiff = $prev ? ($netProfit - ($prev['net_profit'] ?? 0)) : null;
                $netColor = ($netProfit >= 0) ? 'success' : 'danger';
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">Chiffre d'affaires</p>
                            <h4 class="fw-bold text-primary">{{ number_format($data['total_revenue'] ?? 0, 2) }}</h4>
                            @if($prev)
                                <small class="text-muted">Prec: {{ number_format($prev['total_revenue'] ?? 0, 2) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">Coût des ventes (COGS)</p>
                            <h4 class="fw-bold text-warning">{{ number_format($cogs, 2) }}</h4>
                            @if($prev)
                                <small class="text-muted">Prec: {{ number_format($prev['cogs'] ?? 0, 2) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">Marge brute</p>
                            <h4 class="fw-bold text-info">{{ number_format($grossProfit, 2) }}</h4>
                            <span class="badge bg-info bg-opacity-25 text-info">{{ $grossMargin }}%</span>
                            @if($prev)
                                <br><small class="text-muted">Prec: {{ number_format($prev['gross_profit'] ?? 0, 2) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">Charges</p>
                            <h4 class="fw-bold text-danger">{{ number_format($data['total_expenses'] ?? 0, 2) }}</h4>
                            @if($prev)
                                <small class="text-muted">Prec: {{ number_format($prev['total_expenses'] ?? 0, 2) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="card h-100 border-0 bg-{{ $netColor }}-transparent shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">Résultat net</p>
                            <h4 class="fw-bold text-{{ $netColor }}">{{ number_format($netProfit, 2) }}</h4>
                            @if($netDiff !== null)
                                <small class="{{ $netDiff >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $netDiff >= 0 ? '+' : '' }}{{ number_format($netDiff, 2) }} vs prec
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="row">
                        {{-- Revenue Breakdown --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">Revenus</h5>
                            <table class="table">
                                <tbody>
                                    @forelse($data['revenue_lines'] ?? [] as $line)
                                    <tr>
                                        <td>{{ $line['label'] ?? '---' }}</td>
                                        <td class="text-end text-success">{{ number_format($line['amount'] ?? 0, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="2" class="text-center text-muted">Aucune donnee</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold border-top">
                                        <td>Total revenus</td>
                                        <td class="text-end text-success">{{ number_format($data['total_revenue'] ?? 0, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Expense Breakdown --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">Depenses</h5>
                            <table class="table">
                                <tbody>
                                    @forelse($data['expense_lines'] ?? [] as $line)
                                    <tr>
                                        <td>{{ $line['label'] ?? '---' }}</td>
                                        <td class="text-end text-danger">{{ number_format($line['amount'] ?? 0, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="2" class="text-center text-muted">Aucune donnee</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold border-top">
                                        <td>Total depenses</td>
                                        <td class="text-end text-danger">{{ number_format($data['total_expenses'] ?? 0, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
