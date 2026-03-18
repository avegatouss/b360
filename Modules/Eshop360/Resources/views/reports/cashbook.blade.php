@php $slug = $instance->slug ?? ''; @endphp
<x-dashboard::layouts.master
    :title="__('Livre de caisse') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Livre de caisse')">

    <div class="page-wrapper">
        <div class="content">
            {{-- Page Header --}}
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Livre de caisse') }}</h4>
                        <h6>{{ __('Journal des entrees et sorties de tresorerie') }}</h6>
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

            {{-- KPI Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-4 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-success border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-success-transparent rounded-circle">
                                    <i class="ti ti-arrow-down-left fs-20 text-success"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Total entrees') }}</p>
                                    <h4 class="fw-bold mb-0 text-success">{{ number_format($data['total_credit'] ?? $data['total_received'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-danger border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-danger-transparent rounded-circle">
                                    <i class="ti ti-arrow-up-right fs-20 text-danger"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Total sorties') }}</p>
                                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($data['total_debit'] ?? $data['total_expenses'] ?? 0, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-12">
                    @php $closingBalance = $data['closing_balance'] ?? 0; @endphp
                    <div class="card border-0 shadow-sm border-start border-{{ $closingBalance >= 0 ? 'primary' : 'danger' }} border-3 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-{{ $closingBalance >= 0 ? 'primary' : 'danger' }}-transparent rounded-circle">
                                    <i class="ti ti-wallet fs-20 text-{{ $closingBalance >= 0 ? 'primary' : 'danger' }}"></i>
                                </span>
                                <div class="ms-3">
                                    <p class="text-muted mb-1 small">{{ __('Solde de cloture') }}</p>
                                    <h4 class="fw-bold mb-0">{{ number_format($closingBalance, 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- By Method Summary --}}
            @if(!empty($data['by_method']))
            <div class="row g-3 mb-4">
                @foreach($data['by_method'] as $method => $amount)
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">{{ ucfirst(str_replace('_', ' ', $method)) }}</p>
                            <h5 class="fw-bold mb-0">{{ number_format($amount, 0, ',', ' ') }}</h5>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Entries Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-list me-2"></i>{{ __('Journal des ecritures') }}
                        @if(!empty($data['entries']))
                            <span class="badge bg-primary ms-2">{{ count($data['entries']) }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Methode') }}</th>
                                    <th class="text-end">{{ __('Entrees') }}</th>
                                    <th class="text-end">{{ __('Sorties') }}</th>
                                    <th class="text-end">{{ __('Solde') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['entries'] ?? [] as $entry)
                                <tr>
                                    <td><i class="ti ti-calendar-event me-1 text-muted"></i>{{ $entry['date'] ?? '---' }}</td>
                                    <td><code>{{ $entry['reference'] ?? '---' }}</code></td>
                                    <td>{{ $entry['description'] ?? '---' }}</td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $entry['method'] ?? '---')) }}</span></td>
                                    <td class="text-end fw-semibold text-success">
                                        @if(($entry['credit'] ?? 0) > 0)
                                            {{ number_format($entry['credit'], 0, ',', ' ') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-danger">
                                        @if(($entry['debit'] ?? 0) > 0)
                                            {{ number_format($entry['debit'], 0, ',', ' ') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($entry['balance'] ?? 0, 0, ',', ' ') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">{{ __('Aucune donnee') }}</td></tr>
                                @endforelse
                            </tbody>
                            @if(!empty($data['entries']))
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="4">{{ __('Total') }}</td>
                                    <td class="text-end text-success">{{ number_format($data['total_credit'] ?? $data['total_received'] ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end text-danger">{{ number_format($data['total_debit'] ?? $data['total_expenses'] ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($data['closing_balance'] ?? 0, 0, ',', ' ') }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
