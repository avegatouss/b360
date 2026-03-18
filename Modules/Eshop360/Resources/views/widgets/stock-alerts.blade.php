{{-- Widget: Stock Alerts --}}
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $instanceId = $instance?->id ?? 0;
    $slug = $instance->slug ?? '';

    $lowStockCount = \Illuminate\Support\Facades\DB::table('eshop_stocks as s')
        ->join('eshop_products as p', 'p.id', '=', 's.product_id')
        ->where('s.instance_id', $instanceId)
        ->whereColumn('s.quantity', '<=', 'p.alert_quantity')
        ->where('p.is_active', true)
        ->count();

    $expiredCount = \Modules\Eshop360\Models\Product::where('instance_id', $instanceId)
        ->where('is_active', true)
        ->whereNotNull('expiry_date')
        ->where('expiry_date', '<', now())
        ->count();

    $expiringSoonCount = \Modules\Eshop360\Models\Product::where('instance_id', $instanceId)
        ->where('is_active', true)
        ->whereNotNull('expiry_date')
        ->whereBetween('expiry_date', [now(), now()->addDays(30)])
        ->count();

    $totalProducts = \Modules\Eshop360\Models\Product::where('instance_id', $instanceId)->where('is_active', true)->count();
@endphp
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-package me-2 text-warning"></i>Alertes Stock</h6>
        <a href="{{ route('eshop360.stocks.index', $slug) }}" class="btn btn-sm btn-outline-primary">Gerer</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6">
                <div class="text-muted small">Produits actifs</div>
                <div class="fw-bold fs-5">{{ $totalProducts }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted small">Stock faible</div>
                <div class="fw-bold fs-5 {{ $lowStockCount > 0 ? 'text-warning' : 'text-muted' }}">
                    <i class="ti ti-alert-triangle me-1"></i>{{ $lowStockCount }}
                </div>
            </div>
            <div class="col-6">
                <div class="text-muted small">Expires</div>
                <div class="fw-bold fs-5 {{ $expiredCount > 0 ? 'text-danger' : 'text-muted' }}">
                    <i class="ti ti-clock-off me-1"></i>{{ $expiredCount }}
                </div>
            </div>
            <div class="col-6">
                <div class="text-muted small">Expire bientot</div>
                <div class="fw-bold fs-5 {{ $expiringSoonCount > 0 ? 'text-orange' : 'text-muted' }}">
                    <i class="ti ti-clock me-1"></i>{{ $expiringSoonCount }}
                </div>
            </div>
        </div>
    </div>
</div>
