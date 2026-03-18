<x-dashboard::layouts.master
    :title="__('Stock faible') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Stock faible')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Stock faible') }}</h4>
            <h6>{{ __('Produits en dessous de leur seuil d\'alerte') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.stock', $slug) }}" class="btn btn-outline-info btn-sm">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.stocks.index', $slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Tout le stock') }}
        </a>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.stocks.low', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom du produit...') }}">
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
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Qte min') }}</label>
                <input type="number" name="min_quantity" min="0" class="form-control form-control-sm" value="{{ request('min_quantity') }}" placeholder="0">
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Qte max') }}</label>
                <input type="number" name="max_quantity" min="0" class="form-control form-control-sm" value="{{ request('max_quantity') }}" placeholder="∞">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'warehouse_id', 'min_quantity', 'max_quantity']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.stocks.low', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold text-warning"><i class="ti ti-alert-triangle me-2"></i>{{ __('Produits en alerte') }} <span class="badge bg-warning text-dark ms-1">{{ $products->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Categorie') }}</th>
                        <th class="text-center">{{ __('Stock actuel') }}</th>
                        <th class="text-center">{{ __('Seuil alerte') }}</th>
                        <th>{{ __('Detail par entrepot') }}</th>
                        <th>{{ __('Expiration') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $stockLines = $product->stocks
                                ->when(request('warehouse_id'), fn ($c) => $c->where('warehouse_id', (int) request('warehouse_id')));
                            $totalQty = $stockLines->sum('quantity');
                            $isEmpty = $totalQty <= 0;
                            $isExpiring = $product->expiry_date && $product->expiry_date->lte(now()->addDays(30));
                        @endphp
                        <tr>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded" style="width:32px;height:32px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:32px;height:32px;"><i class="ti ti-package text-muted"></i></div>
                                @endif
                            </td>
                            <td class="fw-medium">{{ $product->name }}</td>
                            <td class="small"><code>{{ $product->sku }}</code></td>
                            <td class="small text-muted">{{ $product->category?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $isEmpty ? 'bg-danger' : 'bg-warning text-dark' }} fw-bold fs-6">{{ $totalQty }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $product->alert_quantity }}</span>
                            </td>
                            <td>
                                @foreach($stockLines as $stock)
                                    <div class="small">
                                        <span class="fw-medium">{{ $stock->warehouse?->name ?? '—' }}</span>:
                                        <span class="badge {{ $stock->quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill" style="font-size:.65rem;">{{ $stock->quantity }}</span>
                                        @if($stock->store)
                                            <span class="text-muted">({{ $stock->store->name }})</span>
                                        @endif
                                    </div>
                                @endforeach
                                @if($stockLines->isEmpty())
                                    <span class="text-muted small">{{ __('Aucun stock') }}</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($product->expiry_date)
                                    <span class="{{ $isExpiring ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ $product->expiry_date->format('d/m/Y') }}
                                        @if($isExpiring)
                                            <i class="ti ti-alert-circle ms-1"></i>
                                        @endif
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ti ti-mood-happy fs-1 d-block mb-2 text-success"></i>
                                {{ __('Aucun produit en stock faible. Tout va bien !') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-3">{{ $products->links() }}</div>
        @endif
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-filter').select2({
            theme: 'bootstrap-5', allowClear: true, width: '100%',
        }).on('change', function () { this.closest('form').submit(); });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
