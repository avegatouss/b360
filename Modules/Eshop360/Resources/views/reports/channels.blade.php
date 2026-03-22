@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp
<x-dashboard::layouts.master
    :title="__('Rapport canaux') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Rapport canaux')">


            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Rapport canaux') }}</h4>
                        <h6>{{ __('Performance des canaux de distribution') }}</h6>
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
                            <input type="date" name="from" value="{{ $from ?? now()->startOfMonth()->format('Y-m-d') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">{{ __('Au') }}</label>
                            <input type="date" name="to" value="{{ $to ?? now()->format('Y-m-d') }}" class="form-control form-control-sm">
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
                                    <i class="ti ti-chart-pie fs-20 text-primary"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Marge totale') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($totals['total_margin'] ?? 0, 0, ',', ' ') }}</h4>
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
                                    <i class="ti ti-credit-card fs-20 text-danger"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Part dette') }}</p>
                                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($totals['total_debt'] ?? 0, 0, ',', ' ') }}</h4>
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
                                    <i class="ti ti-building-store fs-20 text-success"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Part canaux') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($totals['total_channel'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-info border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-info-transparent rounded-circle">
                                    <i class="ti ti-user-star fs-20 text-info"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Part proprietaire') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($totals['total_owner'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Per-Channel Table with Margin Bars --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-sitemap me-2"></i>{{ __('Detail par canal') }}
                        <span class="badge bg-primary ms-2">{{ count($data) }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Canal') }}</th>
                                    <th class="text-end">{{ __('Marge totale') }}</th>
                                    <th class="text-end">{{ __('Part dette') }}</th>
                                    <th class="text-end">{{ __('Part canal') }}</th>
                                    <th class="text-end">{{ __('Part proprietaire') }}</th>
                                    <th class="text-end">{{ __('Transactions') }}</th>
                                    <th style="width:150px">{{ __('Repartition') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                @php
                                    $margin = $row['summary']['total_margin'] ?? 0;
                                    $debt = $row['summary']['total_debt'] ?? 0;
                                    $channel = $row['summary']['total_channel'] ?? 0;
                                    $owner = $row['summary']['total_owner'] ?? 0;
                                    $debtPct = $margin > 0 ? ($debt / $margin) * 100 : 0;
                                    $channelPct = $margin > 0 ? ($channel / $margin) * 100 : 0;
                                    $ownerPct = $margin > 0 ? ($owner / $margin) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-semibold">
                                            <i class="ti ti-building-store me-1 text-muted"></i>{{ $row['channel']->name ?? '---' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($margin, 0, ',', ' ') }}</td>
                                    <td class="text-end text-danger">{{ number_format($debt, 0, ',', ' ') }}</td>
                                    <td class="text-end text-success">{{ number_format($channel, 0, ',', ' ') }}</td>
                                    <td class="text-end text-info">{{ number_format($owner, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ $row['summary']['transactions_count'] ?? $row['summary']['count'] ?? 0 }}</td>
                                    <td>
                                        <div class="progress" style="height: 16px;">
                                            <div class="progress-bar bg-danger" style="width: {{ $debtPct }}%" data-bs-toggle="tooltip" title="{{ __('Dette') }}: {{ round($debtPct) }}%"></div>
                                            <div class="progress-bar bg-success" style="width: {{ $channelPct }}%" data-bs-toggle="tooltip" title="{{ __('Canal') }}: {{ round($channelPct) }}%"></div>
                                            <div class="progress-bar bg-info" style="width: {{ $ownerPct }}%" data-bs-toggle="tooltip" title="{{ __('Proprio') }}: {{ round($ownerPct) }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">{{ __('Aucune donnee pour cette periode.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
      

</x-dashboard::layouts.master>
