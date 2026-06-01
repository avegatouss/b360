<x-dashboard::layouts.master
    :title="__('Rapport clients') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Rapport clients')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Navigation tabs --}}
<div class="mb-4">
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('eshop360.customers.report', $slug) }}">
                <i class="ti ti-users me-1"></i>{{ __('Rapport clients') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('eshop360.customers.due-report', $slug) }}">
                <i class="ti ti-alert-circle me-1"></i>{{ __('Rapport impayes') }}
            </a>
        </li>
        <li class="nav-item ms-auto">
            <a class="nav-link" href="{{ route('eshop360.customers.index', $slug) }}">
                <i class="ti ti-arrow-left me-1"></i>{{ __('Liste clients') }}
            </a>
        </li>
    </ul>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="ti ti-users fs-4 text-primary"></i></div>
                <div>
                    <p class="text-muted mb-0 small">{{ __('Total clients') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalCustomers) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="ti ti-user-check fs-4 text-success"></i></div>
                <div>
                    <p class="text-muted mb-0 small">{{ __('Actifs sur la periode') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($activeCustomers) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="ti ti-user-plus fs-4 text-info"></i></div>
                <div>
                    <p class="text-muted mb-0 small">{{ __('Nouveaux clients') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($newCustomers) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.customers.report', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Du') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Au') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom, email...') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i>{{ __('Generer') }}</button>
            </div>
            @if(request()->hasAny(['search','date_from','date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.customers.report', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="ti ti-chart-bar me-2"></i>{{ __('Top clients') }}
            <span class="text-muted fw-normal small ms-2">({{ $dateFrom }} → {{ $dateTo }})</span>
        </h6>
        <a href="{{ route('eshop360.export.customers', $slug) }}" class="btn btn-sm btn-outline-info"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Telephone') }}</th>
                        <th class="text-end">{{ __('Commandes') }}</th>
                        <th class="text-end">{{ __('Panier moyen') }}</th>
                        <th class="text-end">{{ __('Total depense') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $i => $customer)
                        <tr>
                            <td class="text-muted">{{ $topCustomers->firstItem() + $i }}</td>
                            <td>
                                <a href="{{ route('eshop360.customers.show', [$slug, $customer]) }}" class="fw-medium text-decoration-none">{{ $customer->name }}</a>
                                @if($customer->code)
                                    <span class="badge bg-light text-muted ms-1" style="font-size:.6rem;">{{ $customer->code }}</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $customer->email ?? '—' }}</td>
                            <td class="small">{{ $customer->phone ?? '—' }}</td>
                            <td class="text-end"><span class="badge bg-primary-subtle text-primary">{{ number_format($customer->order_count ?? 0) }}</span></td>
                            <td class="text-end small">{{ number_format($customer->avg_order_value ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($customer->total_spent ?? 0, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="ti ti-users-minus fs-1 d-block mb-2"></i>{{ __('Aucun client actif sur cette periode.') }}</td></tr>
                    @endforelse
                </tbody>
                @if($topCustomers->total() > 0)
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="4">{{ __('Total') }}</td>
                        <td class="text-end">{{ number_format($topCustomers->sum('order_count')) }}</td>
                        <td></td>
                        <td class="text-end text-success">{{ number_format($topCustomers->sum('total_spent'), 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @if($topCustomers->hasPages())
        <div class="card-footer">{{ $topCustomers->withQueryString()->links() }}</div>
    @endif
</div>

</x-dashboard::layouts.master>
