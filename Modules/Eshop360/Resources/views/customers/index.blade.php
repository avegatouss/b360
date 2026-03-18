<x-dashboard::layouts.master
    :title="__('Clients') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Clients')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Clients') }}</h4>
            <h6>{{ __('Gerer votre base de clients') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.customers', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.customers.report', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-chart-bar me-1"></i>{{ __('Rapport') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-customer"><i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter un client') }}</button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.customers.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom, email, tel, code...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Ville') }}</label>
                <input type="text" name="city" class="form-control form-control-sm" value="{{ request('city') }}" placeholder="{{ __('Ville...') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search','is_active','city']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.customers.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-users me-2"></i>{{ __('Clients') }} <span class="badge bg-primary ms-1">{{ $customers->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Telephone') }}</th>
                        <th>{{ __('Ville') }}</th>
                        <th class="text-center">{{ __('Commandes') }}</th>
                        <th class="text-end">{{ __('Total depense') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:130px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="small"><code>{{ $customer->code }}</code></td>
                            <td class="fw-medium"><a href="{{ route('eshop360.customers.show', [$slug, $customer]) }}" class="text-decoration-none">{{ $customer->name }}</a></td>
                            <td class="small text-muted">{{ $customer->email ?? '—' }}</td>
                            <td class="small">{{ $customer->phone ?? '—' }}</td>
                            <td class="small">{{ $customer->city ?? '—' }}</td>
                            <td class="text-center">
                                @if($customer->orders_count > 0)
                                    <span class="badge bg-primary-subtle text-primary">{{ $customer->orders_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold {{ ($customer->total_spent ?? 0) > 0 ? 'text-success' : '' }}">{{ number_format($customer->total_spent ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                @if($customer->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactif') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.customers.show', [$slug, $customer]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Detail') }}"><i class="ti ti-eye"></i></a>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-customer-{{ $customer->id }}" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></button>
                                    <form action="{{ route('eshop360.customers.destroy', [$slug, $customer]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce client ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="ti ti-users-minus fs-1 d-block mb-2"></i>{{ __('Aucun client trouve.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())<div class="p-3">{{ $customers->links() }}</div>@endif
    </div>
</div>

{{-- Add Customer Modal --}}
<div class="modal fade" id="add-customer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">{{ __('Nouveau client') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('eshop360.customers.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label><input type="email" name="email" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Telephone') }}</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Pays') }}</label><input type="text" name="country" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Ville') }}</label><input type="text" name="city" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Adresse') }}</label><input type="text" name="address" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary">{{ __('Creer') }}</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Customer Modals --}}
@foreach($customers as $customer)
<div class="modal fade" id="edit-customer-{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">{{ __('Modifier') }}: {{ $customer->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('eshop360.customers.update', [$slug, $customer]) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ $customer->name }}" required></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label><input type="email" name="email" class="form-control" value="{{ $customer->email }}"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Telephone') }}</label><input type="text" name="phone" class="form-control" value="{{ $customer->phone }}"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Pays') }}</label><input type="text" name="country" class="form-control" value="{{ $customer->country }}"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Ville') }}</label><input type="text" name="city" class="form-control" value="{{ $customer->city }}"></div>
                        <div class="col-md-6"><label class="form-label">{{ __('Adresse') }}</label><input type="text" name="address" class="form-control" value="{{ $customer->address }}"></div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-3">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($customer->is_active) id="edit-cust-{{ $customer->id }}">
                                <label class="form-check-label" for="edit-cust-{{ $customer->id }}">{{ __('Actif') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button></div>
            </form>
        </div>
    </div>
</div>
@endforeach

</x-dashboard::layouts.master>
