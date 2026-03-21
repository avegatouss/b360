@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Rapport produits') }}</h4>
    <p class="text-muted mb-0">{{ __('Produits les plus vendus sur ce canal') }}</p>
</div>

{{-- Date Range Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Du') }}</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Au') }}</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-filter me-1"></i> {{ __('Filtrer') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Top Products Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">{{ __('Top produits') }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th class="text-center">{{ __('Quantite vendue') }}</th>
                        <th class="text-end">{{ __('Chiffre d\'affaires') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts ?? [] as $index => $product)
                    <tr>
                        <td>
                            @if($index < 3)
                                <span class="badge bg-{{ ['warning','secondary','dark'][$index] }}">{{ $index + 1 }}</span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </td>
                        <td class="fw-medium">{{ $product->name ?? $product->product_name ?? '---' }}</td>
                        <td><code>{{ $product->sku ?? '---' }}</code></td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6">{{ $product->total_qty ?? 0 }}</span>
                        </td>
                        <td class="text-end fw-bold">{{ number_format($product->total_revenue ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">{{ __('Aucune donnee disponible.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
