<x-dashboard::layouts.master
    :title="__('Entrepôts et magasins') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Entrepôts et magasins')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-building-warehouse me-2"></i>{{ __('Entrepôts et magasins') }}</h4>
        <p class="text-muted mb-0">{{ __('Gérer vos lieux de stockage et points de vente') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.export.stock', $slug) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-warehouse">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel entrepôt') }}
        </button>
    </div>
</div>

{{-- Global KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-building-warehouse text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $totalWarehouses }}</h3>
                        <span class="text-muted">{{ __('Entrepôts') }}</span>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success-subtle text-success">{{ $activeWarehouses }} {{ __('actifs') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-packages text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($totalStockUnits, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Unités en stock') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-package text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $totalProducts }}</h3>
                        <span class="text-muted">{{ __('Produits stockés') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-{{ $lowStockCount > 0 ? 'danger' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-alert-triangle text-{{ $lowStockCount > 0 ? 'danger' : 'secondary' }} fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ $lowStockCount > 0 ? 'text-danger' : '' }}">{{ $lowStockCount }}</h3>
                        <span class="text-muted">{{ __('Stock faible') }}</span>
                    </div>
                </div>
                @if($lowStockCount > 0)
                <div class="mt-2">
                    <a href="{{ route('eshop360.stocks.low', $slug) }}" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size:11px;">
                        <i class="ti ti-eye me-1"></i>{{ __('Voir') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.warehouses.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom, code ou ville...') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="is_active" class="form-select form-select-sm wh-select2" data-placeholder="{{ __('Statut') }}">
                    <option value=""></option>
                    <option value="1" @selected(request('is_active') === '1')>{{ __('Actif') }}</option>
                    <option value="0" @selected(request('is_active') === '0')>{{ __('Inactif') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['search', 'is_active']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.warehouses.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Réinitialiser') }}</a>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Warehouse Cards --}}
<div class="row g-3">
    @forelse($warehouses as $warehouse)
        @php
            $totalUnits = $warehouse->stocks_sum_quantity ?? 0;
            $stockPercent = $totalStockUnits > 0 ? round(($totalUnits / $totalStockUnits) * 100, 1) : 0;
        @endphp
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                {{-- Header --}}
                <div class="card-header bg-transparent py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded bg-primary-subtle d-flex align-items-center justify-content-center" style="width:52px;height:52px;border-radius:12px!important;">
                                <i class="ti ti-building-warehouse fs-3 text-primary"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">{{ $warehouse->name }}</h5>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="small">{{ $warehouse->code }}</code>
                                    @if($warehouse->city)
                                        <span class="text-muted small"><i class="ti ti-map-pin" style="font-size:12px;"></i> {{ $warehouse->city }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($warehouse->is_active)
                                <span class="badge bg-success">{{ __('Actif') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Inactif') }}</span>
                            @endif
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-wh-{{ $warehouse->id }}"><i class="ti ti-edit me-2"></i>{{ __('Modifier') }}</button></li>
                                    <li><a class="dropdown-item" href="{{ route('eshop360.stocks.index', [$slug, 'warehouse_id' => $warehouse->id]) }}"><i class="ti ti-package me-2"></i>{{ __('Voir le stock') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('eshop360.stock-transfers.index', [$slug, 'from_warehouse_id' => $warehouse->id]) }}"><i class="ti ti-transfer me-2"></i>{{ __('Transferts') }}</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('eshop360.warehouses.destroy', [$slug, $warehouse]) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer cet entrepôt et ses magasins ?') }}')">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="ti ti-trash me-2"></i>{{ __('Supprimer') }}</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="card-body py-3">
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="ti ti-packages text-success me-1"></i>
                                </div>
                                <h3 class="fw-bold mb-0 text-success">{{ number_format($totalUnits, 0, ',', ' ') }}</h3>
                                <small class="text-muted d-block mt-1">{{ __('Unités') }}</small>
                                @if($stockPercent > 0)
                                    <div class="progress mt-2" style="height:4px;">
                                        <div class="progress-bar bg-success" style="width:{{ min($stockPercent, 100) }}%"></div>
                                    </div>
                                    <small class="text-muted" style="font-size:10px;">{{ $stockPercent }}% {{ __('du total') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="ti ti-list-numbers text-primary me-1"></i>
                                </div>
                                <h3 class="fw-bold mb-0 text-primary">{{ $warehouse->stocks_count }}</h3>
                                <small class="text-muted d-block mt-1">{{ __('Lignes stock') }}</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="ti ti-store text-info me-1"></i>
                                </div>
                                <h3 class="fw-bold mb-0 text-info">{{ $warehouse->stores_count }}</h3>
                                <small class="text-muted d-block mt-1">{{ __('Magasins') }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- Contact info --}}
                    @if($warehouse->address || $warehouse->phone || $warehouse->manager_id || $warehouse->manager_name || $warehouse->email)
                        <div class="border-top pt-3">
                            <div class="row g-2">
                                @if($warehouse->address)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="ti ti-map-pin text-muted mt-1" style="font-size:14px;"></i>
                                            <small class="text-muted">{{ $warehouse->address }}</small>
                                        </div>
                                    </div>
                                @endif
                                @if($warehouse->phone)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ti ti-phone text-muted" style="font-size:14px;"></i>
                                            <small class="text-muted">{{ $warehouse->phone }}</small>
                                        </div>
                                    </div>
                                @endif
                                @if($warehouse->email)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ti ti-mail text-muted" style="font-size:14px;"></i>
                                            <small class="text-muted">{{ $warehouse->email }}</small>
                                        </div>
                                    </div>
                                @endif
                                @if($warehouse->manager)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ti ti-user text-muted" style="font-size:14px;"></i>
                                            <small class="fw-medium">{{ $warehouse->manager->name }}</small>
                                            @if($warehouse->manager->position)
                                                <small class="text-muted">— {{ $warehouse->manager->position }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($warehouse->manager_name)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ti ti-user text-muted" style="font-size:14px;"></i>
                                            <small class="fw-medium">{{ $warehouse->manager_name }}</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Stores --}}
                    @php $stores = $warehouse->stores ?? collect(); @endphp
                    @if($stores->isNotEmpty())
                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold small text-muted"><i class="ti ti-store me-1"></i>{{ __('Magasins rattachés') }}</span>
                                <span class="badge bg-info-subtle text-info">{{ $stores->count() }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($stores as $store)
                                    <div class="border rounded-2 px-3 py-2 d-flex align-items-center gap-2 bg-light">
                                        <i class="ti ti-store text-primary" style="font-size:14px;"></i>
                                        <div>
                                            <span class="fw-medium small">{{ $store->name }}</span>
                                            @if($store->code)
                                                <small class="text-muted ms-1">({{ $store->code }})</small>
                                            @endif
                                        </div>
                                        @if(!$store->is_active)
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size:9px;">{{ __('inactif') }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer Actions --}}
                <div class="card-footer bg-transparent py-2">
                    <div class="d-flex gap-2">
                        <a href="{{ route('eshop360.stocks.index', [$slug, 'warehouse_id' => $warehouse->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-package me-1"></i>{{ __('Voir le stock') }}
                        </a>
                        <a href="{{ route('eshop360.stock-transfers.index', [$slug, 'from_warehouse_id' => $warehouse->id]) }}" class="btn btn-sm btn-outline-info">
                            <i class="ti ti-transfer me-1"></i>{{ __('Transferts') }}
                        </a>
                        <button class="btn btn-sm btn-outline-secondary ms-auto" data-bs-toggle="modal" data-bs-target="#edit-wh-{{ $warehouse->id }}">
                            <i class="ti ti-edit me-1"></i>{{ __('Modifier') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div class="modal fade" id="edit-wh-{{ $warehouse->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-edit me-2"></i>{{ __('Modifier') }}: {{ $warehouse->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('eshop360.warehouses.update', [$slug, $warehouse]) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $warehouse->name }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" value="{{ $warehouse->code }}" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">{{ __('Adresse') }}</label>
                                    <input type="text" name="address" class="form-control" value="{{ $warehouse->address }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Ville') }}</label>
                                    <input type="text" name="city" class="form-control" value="{{ $warehouse->city }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Téléphone') }}</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $warehouse->phone }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Email') }}</label>
                                    <input type="email" name="email" class="form-control" value="{{ $warehouse->email }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Responsable') }}</label>
                                    <select name="manager_id" class="form-select select2-wh-modal">
                                        <option value="">{{ __('Aucun') }}</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" @selected($warehouse->manager_id == $emp->id)>{{ $emp->name }} — {{ $emp->position ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($warehouse->is_active) id="edit-wh-active-{{ $warehouse->id }}">
                                        <label class="form-check-label" for="edit-wh-active-{{ $warehouse->id }}">{{ __('Actif') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                            <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Enregistrer') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="ti ti-building-warehouse fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-2">{{ __('Aucun entrepôt trouvé.') }}</p>
                    @if(request()->hasAny(['search', 'is_active']))
                        <a href="{{ route('eshop360.warehouses.index', $slug) }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="ti ti-x me-1"></i>{{ __('Réinitialiser les filtres') }}
                        </a>
                    @endif
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add-warehouse">
                        <i class="ti ti-circle-plus me-1"></i>{{ __('Créer un entrepôt') }}
                    </button>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($warehouses->hasPages())
    <div class="mt-3">{{ $warehouses->links() }}</div>
@endif

{{-- Add Warehouse Modal --}}
<div class="modal fade" id="add-warehouse" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-building-warehouse me-2"></i>{{ __('Nouvel entrepôt') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.warehouses.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Nom de l\'entrepôt') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="{{ __('Ex: Entrepôt Central') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required placeholder="WH-001">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Adresse') }}</label>
                            <input type="text" name="address" class="form-control" placeholder="{{ __('Adresse complète') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Ville') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Téléphone') }}</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Responsable') }}</label>
                            <select name="manager_id" class="form-select select2-wh-modal">
                                <option value="">{{ __('Aucun') }}</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->position ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="add-wh-active">
                                <label class="form-check-label" for="add-wh-active">{{ __('Actif') }}</label>
                            </div>
                        </div>
                    </div>

                    {{-- Inline stores --}}
                    <hr class="my-3">
                    <h6 class="fw-bold"><i class="ti ti-store me-1"></i>{{ __('Magasins (optionnel)') }}</h6>
                    <p class="text-muted small mb-2">{{ __('Ajoutez des magasins/points de vente rattachés à cet entrepôt.') }}</p>
                    <div id="stores-container"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-store-btn">
                        <i class="ti ti-plus me-1"></i>{{ __('Ajouter un magasin') }}
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Créer l\'entrepôt') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.wh-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });

    // Select2 dans les modals (responsable)
    $('.select2-wh-modal').each(function () {
        var $el = $(this), $modal = $el.closest('.modal');
        $el.select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: @json(__('Selectionner un responsable')), dropdownParent: $modal.length ? $modal : undefined });
    });
});
</script>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function () {
    var storeIndex = 0;
    document.getElementById('add-store-btn').addEventListener('click', function () {
        var container = document.getElementById('stores-container');
        var html = '<div class="row g-2 mb-2 align-items-end store-row">' +
            '<div class="col-md-4"><label class="form-label small mb-1">Nom *</label><input type="text" name="stores[' + storeIndex + '][name]" class="form-control form-control-sm" required></div>' +
            '<div class="col-md-3"><label class="form-label small mb-1">Code *</label><input type="text" name="stores[' + storeIndex + '][code]" class="form-control form-control-sm" required></div>' +
            '<div class="col-md-4"><label class="form-label small mb-1">Adresse</label><input type="text" name="stores[' + storeIndex + '][address]" class="form-control form-control-sm"></div>' +
            '<div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-store"><i class="ti ti-x"></i></button></div>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);
        storeIndex++;
    });
    document.getElementById('stores-container').addEventListener('click', function (e) {
        if (e.target.closest('.remove-store')) {
            e.target.closest('.store-row').remove();
        }
    });
});
</script>

</x-dashboard::layouts.master>
