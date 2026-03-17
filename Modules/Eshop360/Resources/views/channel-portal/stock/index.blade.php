@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Stock') }}</h4>
    <p class="text-muted mb-0">
        Niveaux de stock des produits du canal
        @if($warehouseId)
            (Entrepot #{{ $warehouseId }})
        @else
            <span class="text-warning">{{ __('(Aucun entrepot associe)') }}</span>
        @endif
    </p>
</div>

{{-- Search --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">{{ __('Rechercher un produit') }}</label>
                <input type="text" name="search" class="form-control" placeholder="{{ __('Nom ou SKU...') }}" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-search me-1"></i> {{ __('Rechercher') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Stock Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Categorie') }}</th>
                        <th>{{ __('Marque') }}</th>
                        <th>{{ __('Prix canal') }}</th>
                        <th class="text-center">{{ __('Stock') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    @php
                        $qty = $stocks[$product->id] ?? null;
                    @endphp
                    <tr>
                        <td class="fw-medium">{{ $product->name }}</td>
                        <td><code>{{ $product->sku ?? '—' }}</code></td>
                        <td>{{ $product->category->name ?? '—' }}</td>
                        <td>{{ $product->brand->name ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($product->pivot->sale_price ?? 0, 0, ',', ' ') }} XAF</td>
                        <td class="text-center">
                            @if($warehouseId)
                                @if($qty !== null)
                                    <span class="badge bg-{{ $qty > 0 ? ($qty <= 5 ? 'warning' : 'success') : 'danger' }} fs-6">
                                        {{ $qty }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            @else
                                <span class="text-muted">{{ __('N/A') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ __('Aucun produit dans le catalogue de ce canal.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $products->links() }}
</div>
@endsection
