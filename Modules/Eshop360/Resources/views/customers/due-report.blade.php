<x-dashboard::layouts.master
    :title="__('eshop::eshop.customer_due_report') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="{{ __('eshop::eshop.customer_due_report') }}">

{{-- Navigation tabs --}}
<div class="mb-4">
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('eshop360.customers.report', $instance->slug) }}">
                <i class="ti ti-users me-1"></i>{{ __('eshop::eshop.customer_report') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('eshop360.customers.due-report', $instance->slug) }}">
                <i class="ti ti-alert-circle me-1"></i>{{ __('eshop::eshop.customer_due_report') }}
            </a>
        </li>
    </ul>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                    <i class="ti ti-coin-off fs-22 text-danger"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">{{ __('Total créances') }}</p>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($totalDueAmount, 0, ',', ' ') }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="ti ti-users fs-22 text-warning"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">{{ __('Clients avec créances') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($customersDueCount) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Form --}}
<div class="card mb-3">
    <div class="card-body pb-1">
        <form method="GET" action="{{ route('eshop360.customers.due-report', $instance->slug) }}">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Recherche client') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('Nom du client…') }}" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Montant min. dû') }}</label>
                    <input type="number" name="min_due" class="form-control" placeholder="0" value="{{ request('min_due') }}" min="0">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100 d-block" type="submit">
                        <i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}
                    </button>
                </div>
                @if(request()->anyFilled(['search', 'min_due']))
                <div class="col-md-2 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <a href="{{ route('eshop360.customers.due-report', $instance->slug) }}" class="btn btn-outline-secondary w-100 d-block">
                        <i class="ti ti-x me-1"></i>{{ __('Réinitialiser') }}
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Results table --}}
<div class="card no-search">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="mb-0">
            {{ __('Clients avec solde impayé') }}
            @if($customersWithDue->total() > 0)
                <span class="badge bg-danger ms-1">{{ $customersWithDue->total() }}</span>
            @endif
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
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Email / Tél') }}</th>
                        <th class="text-center">{{ __('Commandes impayées') }}</th>
                        <th>{{ __('Première créance') }}</th>
                        <th class="text-end">{{ __('Total dû') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customersWithDue as $customer)
                    @php
                        $dueDays = $customer->oldest_due_date
                            ? now()->diffInDays(\Carbon\Carbon::parse($customer->oldest_due_date))
                            : null;
                        $badgeClass = $dueDays === null ? 'bg-secondary'
                            : ($dueDays > 60 ? 'bg-danger' : ($dueDays > 30 ? 'bg-warning text-dark' : 'bg-info'));
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('eshop360.customers.show', [$instance->slug, $customer]) }}" class="fw-semibold text-dark">
                                {{ $customer->name }}
                            </a>
                            @if($customer->code)
                                <span class="badge bg-light text-muted ms-1 fs-10">{{ $customer->code }}</span>
                            @endif
                        </td>
                        <td class="text-muted fs-12">
                            @if($customer->email) <div>{{ $customer->email }}</div> @endif
                            @if($customer->phone) <div>{{ $customer->phone }}</div> @endif
                            @if(!$customer->email && !$customer->phone) — @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">{{ $customer->unpaid_orders ?? 0 }}</span>
                        </td>
                        <td>
                            @if($customer->oldest_due_date)
                                <span class="badge {{ $badgeClass }}">
                                    {{ \Carbon\Carbon::parse($customer->oldest_due_date)->format('d/m/Y') }}
                                    @if($dueDays !== null) ({{ $dueDays }}j) @endif
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end fw-bold text-danger fs-15">
                            {{ number_format($customer->total_due ?? 0, 0, ',', ' ') }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('eshop360.customers.show', [$instance->slug, $customer]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="ti ti-circle-check fs-24 d-block mb-2 text-success"></i>
                            {{ __('Aucune créance client en cours.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($customersWithDue->total() > 0)
                <tfoot>
                    <tr class="bg-light fw-bold">
                        <td colspan="4">{{ __('Total') }}</td>
                        <td class="text-end text-danger">
                            {{ number_format($customersWithDue->sum('total_due'), 0, ',', ' ') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @if($customersWithDue->hasPages())
    <div class="card-footer">
        {{ $customersWithDue->withQueryString()->links() }}
    </div>
    @endif
</div>

</x-dashboard::layouts.master>
