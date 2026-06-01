<x-dashboard::layouts.master
    :title="__('Clients') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Clients')">

@php
    $slug = $instance->slug ?? '';
    $totalCustomers = $customers->total();
    $collection = $customers->getCollection();
    $activeCount = $collection->where('is_active', true)->count();
    $totalOrders = $collection->sum('orders_count');
    $totalSpent = $collection->sum('total_spent');
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

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

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-users fs-4 text-primary"></i></div>
                <div><div class="text-muted small">{{ __('Total clients') }}</div><div class="fs-4 fw-bold">{{ $totalCustomers }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-user-check fs-4 text-success"></i></div>
                <div><div class="text-muted small">{{ __('Clients actifs') }}</div><div class="fs-4 fw-bold text-success">{{ $activeCount }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-shopping-cart fs-4 text-info"></i></div>
                <div><div class="text-muted small">{{ __('Total commandes') }}</div><div class="fs-4 fw-bold text-info">{{ $totalOrders }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-currency-dollar fs-4 text-warning"></i></div>
                <div><div class="text-muted small">{{ __('Total depense') }}</div><div class="fs-4 fw-bold text-warning">{{ number_format($totalSpent, 0, ',', ' ') }}</div></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.customers.index', $slug) }}" id="customer-filter-form" class="row g-2 align-items-center">
            <div class="col">
                <input type="text" name="search" id="customer-search-input" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom, email, tel, code...') }}" autocomplete="off">
            </div>
            <div class="col-auto" style="min-width: 130px;">
                <select name="is_active" class="form-select form-select-sm idx-select2" data-placeholder="{{ __('Statut') }}">
                    <option value=""></option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                </select>
            </div>
            <div class="col-auto" style="min-width: 150px;">
                <select name="city" class="form-select form-select-sm idx-select2" data-placeholder="{{ __('Ville') }}">
                    <option value=""></option>
                    @foreach($collection->pluck('city')->filter()->unique()->sort() as $city)
                        <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['search', 'is_active', 'city']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.customers.index', $slug) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Effacer filtres') }}"><i class="ti ti-x"></i></a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted small">{{ $totalCustomers }} {{ __('client(s)') }}</span>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Customers Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Ville') }}</th>
                        <th class="text-end">{{ __('Portefeuille') }}</th>
                        <th class="text-center">{{ __('Commandes') }}</th>
                        <th class="text-end">{{ __('Total depense') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>
                                @if($customer->image)
                                    <img src="{{ asset('storage/' . $customer->image) }}" class="rounded-circle" style="width:42px;height:42px;object-fit:cover;">
                                @else
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                        <span class="fw-bold text-primary">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('eshop360.customers.show', [$slug, $customer]) }}" class="fw-semibold text-decoration-none d-block">{{ $customer->name }}</a>
                                @if($customer->company_name)
                                    <small class="text-muted">{{ $customer->company_name }}</small>
                                @endif
                                <code class="d-block text-muted" style="font-size:.75rem;">{{ $customer->code }}</code>
                            </td>
                            <td>
                                <div class="small">{{ $customer->email ?? '—' }}</div>
                                <div class="small text-muted">{{ $customer->phone ?? '—' }}</div>
                            </td>
                            <td class="small">{{ $customer->city ?? '—' }}</td>
                            <td class="text-end">
                                @if(($customer->wallet_balance ?? 0) > 0)
                                    <span class="fw-bold text-success">{{ number_format($customer->wallet_balance, 0, ',', ' ') }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($customer->orders_count > 0)
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $customer->orders_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold {{ ($customer->total_spent ?? 0) > 0 ? 'text-success' : '' }}">{{ number_format($customer->total_spent ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                @if($customer->is_active)
                                    <span class="badge bg-success-subtle text-success rounded-pill">{{ __('Actif') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ __('Inactif') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.customers.show', [$slug, $customer]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Detail') }}"><i class="ti ti-eye"></i></a>
                                    <a href="{{ route('eshop360.customers.show', [$slug, $customer]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></a>
                                    <form action="{{ route('eshop360.customers.destroy', [$slug, $customer]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce client ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="ti ti-users-minus fs-1 d-block mb-2"></i>
                                {{ __('Aucun client trouve.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-3 border-top">{{ $customers->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Customer Modal --}}
<div class="modal fade" id="add-customer" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="ti ti-user-plus me-2"></i>{{ __('Nouveau client') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.customers.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Entreprise') }}</label>
                            <input type="text" name="company_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Telephone') }}</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Pays') }}</label>
                            <input type="text" name="country" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Ville') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Adresse') }}</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Limite de credit') }}</label>
                            <div class="input-group">
                                <input type="number" name="credit_limit" class="form-control" min="0" step="1" placeholder="0">
                                <span class="input-group-text">{{ $eshopCurrency ?? 'FCFA' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-cust-active">
                                <label class="form-check-label" for="add-cust-active">{{ __('Client actif') }}</label>
                            </div>
                        </div>

                        {{-- Create user account --}}
                        <div class="col-12 border-top pt-3 mt-2">
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="create_user_account" value="0">
                                <input class="form-check-input" type="checkbox" name="create_user_account" value="1" id="create-user-toggle">
                                <label class="form-check-label fw-bold" for="create-user-toggle">
                                    <i class="ti ti-user-plus me-1"></i>{{ __('Creer un compte utilisateur') }}
                                </label>
                            </div>
                            <span class="text-muted mb-2 d-block">{{ __('Permet au client de se connecter au portail en ligne.') }}</span>
                            <div id="user-account-fields" style="display:none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Mot de passe') }} <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" minlength="8" placeholder="{{ __('Minimum 8 caracteres') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Confirmer') }} <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control" minlength="8">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
jQuery(function ($) {
    // ── Toggle user account fields ──
    $('#create-user-toggle').on('change', function () {
        $('#user-account-fields').toggle(this.checked);
        $('#user-account-fields input').prop('required', this.checked);
    });

    // ── Select2 filters with auto-submit ──
    $('.idx-select2').each(function () {
        $(this).select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            placeholder: $(this).data('placeholder') || ''
        }).on('select2:select select2:clear', function () {
            $(this).closest('form')[0].submit();
        });
    });

    // ── Auto-search with debounce ──
    var searchTimer = null;
    $('#customer-search-input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            $('#customer-filter-form')[0].submit();
        }, 500);
    }).on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimer);
            $('#customer-filter-form')[0].submit();
        }
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
