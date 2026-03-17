<x-dashboard::layouts.master
    :title="__('eshop::eshop.customer_report') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="{{ __('eshop::eshop.customer_report') }}">

{{-- Navigation tabs --}}
<div class="mb-4">
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('eshop360.customers.report', $instance->slug) }}">
                <i class="ti ti-users me-1"></i>{{ __('eshop::eshop.customer_report') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('eshop360.customers.due-report', $instance->slug) }}">
                <i class="ti ti-alert-circle me-1"></i>{{ __('eshop::eshop.customer_due_report') }}
            </a>
        </li>
    </ul>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="ti ti-users fs-22 text-primary"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">{{ __('Total Customers') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalCustomers) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="ti ti-user-check fs-22 text-success"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">{{ __('Active on Period') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($activeCustomers) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="ti ti-user-plus fs-22 text-info"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">{{ __('New Customers') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($newCustomers) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Form --}}
<div class="card mb-3">
    <div class="card-body pb-1">
        <form method="GET" action="{{ route('eshop360.customers.report', $instance->slug) }}">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Date de début') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Date de fin') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Recherche client') }}</label>
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Nom, email…') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="ti ti-filter me-1"></i>{{ __('Générer le rapport') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Results table --}}
<div class="card no-search">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="mb-0">{{ __('Top Clients') }}
            <span class="text-muted fs-13 fw-normal ms-1">({{ $dateFrom }} → {{ $dateTo }})</span>
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('eshop360.export.customers', $instance->slug) }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-download me-1"></i>Export
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Téléphone') }}</th>
                        <th class="text-end">{{ __('Commandes') }}</th>
                        <th class="text-end">{{ __('Panier moyen') }}</th>
                        <th class="text-end">{{ __('Total dépensé') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $i => $customer)
                    <tr>
                        <td class="text-muted">{{ $topCustomers->firstItem() + $i }}</td>
                        <td>
                            <a href="{{ route('eshop360.customers.show', [$instance->slug, $customer]) }}" class="fw-semibold text-dark">
                                {{ $customer->name }}
                            </a>
                            @if($customer->code)
                                <span class="badge bg-light text-muted ms-1 fs-10">{{ $customer->code }}</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $customer->email ?? '—' }}</td>
                        <td class="text-muted">{{ $customer->phone ?? '—' }}</td>
                        <td class="text-end">{{ number_format($customer->order_count ?? 0) }}</td>
                        <td class="text-end">{{ number_format($customer->avg_order_value ?? 0, 0, ',', ' ') }}</td>
                        <td class="text-end fw-semibold text-success">
                            {{ number_format($customer->total_spent ?? 0, 0, ',', ' ') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="ti ti-users-off fs-24 d-block mb-2"></i>
                            {{ __('Aucun client actif sur cette période.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($topCustomers->total() > 0)
                <tfoot>
                    <tr class="bg-light fw-bold">
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
    <div class="card-footer">
        {{ $topCustomers->withQueryString()->links() }}
    </div>
    @endif
</div>

</x-dashboard::layouts.master>
