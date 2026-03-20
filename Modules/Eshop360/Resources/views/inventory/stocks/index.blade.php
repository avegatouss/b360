<x-dashboard::layouts.master
    :title="__('Gestion des stocks') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Gestion des stocks')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Gestion des stocks') }}</h4>
            <h6>{{ __('Suivre les niveaux de stock par entrepot et magasin') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.stock', $slug) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.stocks.low', $slug) }}" class="btn btn-outline-warning btn-sm">
            <i class="ti ti-alert-triangle me-1"></i>{{ __('Stock faible') }}
        </a>
        <a href="{{ route('eshop360.stock-adjustments.index', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-adjustments me-1"></i>{{ __('Ajustements') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-stock">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter du stock') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.stocks.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom ou SKU du produit...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Entrepot') }}</label>
                <select name="warehouse_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les entrepots') }}</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string) request('warehouse_id') === (string) $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Magasin') }}</label>
                <select name="store_id" class="form-select form-select-sm select2-filter" data-placeholder="{{ __('Tous') }}">
                    <option value="">{{ __('Tous les magasins') }}</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="low_stock" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label small text-warning fw-medium" for="low_stock">{{ __('Stock faible uniquement') }}</label>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'warehouse_id', 'store_id', 'low_stock']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.stocks.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-package me-2"></i>{{ __('Niveaux de stock') }} <span class="badge bg-primary ms-1">{{ $stocks->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Entrepot') }}</th>
                        <th>{{ __('Magasin') }}</th>
                        <th class="text-center">{{ __('Quantite') }}</th>
                        <th class="text-center">{{ __('Reserve') }}</th>
                        <th class="text-center">{{ __('Disponible') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        @php
                            $alertQty = $stock->product?->alert_quantity ?? 5;
                            $available = $stock->quantity - ($stock->reserved_quantity ?? 0);
                            $isLow = $stock->quantity <= $alertQty && $stock->quantity > 0;
                            $isEmpty = $stock->quantity <= 0;
                        @endphp
                        <tr class="{{ $isEmpty ? 'table-danger' : ($isLow ? 'table-warning' : '') }}" style="--bs-table-bg-type: {{ $isEmpty ? 'rgba(220,53,69,.05)' : ($isLow ? 'rgba(255,193,7,.05)' : 'transparent') }}">
                            <td>
                                @if($stock->product?->image)
                                    <img src="{{ asset('storage/' . $stock->product->image) }}" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:32px;height:32px;"><i class="ti ti-package text-muted"></i></div>
                                @endif
                            </td>
                            <td class="fw-medium">{{ $stock->product?->name ?? '—' }}</td>
                            <td class="small text-muted"><code>{{ $stock->product?->sku ?? '—' }}</code></td>
                            <td class="small">{{ $stock->warehouse?->name ?? '—' }}</td>
                            <td class="small">{{ $stock->store?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $isEmpty ? 'bg-danger' : ($isLow ? 'bg-warning text-dark' : 'bg-success') }} fw-bold">{{ $stock->quantity }}</span>
                            </td>
                            <td class="text-center text-muted">{{ $stock->reserved_quantity ?? 0 }}</td>
                            <td class="text-center fw-bold {{ $available <= 0 ? 'text-danger' : 'text-success' }}">{{ $available }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-stock-{{ $stock->id }}" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></button>
                                    <form action="{{ route('eshop360.stocks.destroy', [$slug, $stock]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette entree de stock ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                                {{ __('Aucune entree de stock trouvee.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
            <div class="p-3">{{ $stocks->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Stock Modal --}}
<div class="modal fade" id="add-stock" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Ajouter du stock') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('eshop360.stocks.store', $slug) }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Produit') }} <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select select2-modal" required>
                                <option value="">{{ __('Selectionner un produit') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Entrepot') }} <span class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select select2-modal" required>
                                <option value="">{{ __('Selectionner') }}</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Magasin') }}</label>
                            <select name="store_id" class="form-select">
                                <option value="">{{ __('Aucun') }}</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Quantite') }} <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" value="1" min="1" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control" placeholder="{{ __('Motif optionnel') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Ajouter') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Stock Modals --}}
@foreach($stocks as $stock)
<div class="modal fade" id="edit-stock-{{ $stock->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Modifier le stock') }}: {{ $stock->product?->name ?? '—' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('eshop360.stocks.update', [$slug, $stock]) }}">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Quantite') }}</label>
                            <input type="number" name="quantity" value="{{ $stock->quantity }}" min="0" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Reserve') }}</label>
                            <input type="number" name="reserved_quantity" value="{{ $stock->reserved_quantity ?? 0 }}" min="0" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Raison de l\'ajustement') }}</label>
                            <input type="text" name="reason" class="form-control" placeholder="{{ __('Motif du changement') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.select2-filter').select2({
        theme: 'bootstrap-5', allowClear: true, width: '100%',
    }).on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });

    $('.select2-modal').each(function () {
        var $el = $(this), $modal = $el.closest('.modal');
        $el.select2({ theme: 'bootstrap-5', dropdownParent: $modal.length ? $modal : undefined, width: '100%' });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
