{{-- POS Product Grid --}}
<div class="pos-product-grid">
    <div class="row g-2">
        @forelse($products as $product)
            @php
                $availableQty = (int) $product->stocks->sum('quantity');
                $outOfStock = $availableQty <= 0;
                $alertQty = $product->alert_quantity ?? 5;
                $stockClass = $outOfStock ? 'bg-danger' : ($availableQty <= $alertQty ? 'bg-warning' : 'bg-success');
                $hasVariations = $product->relationLoaded('variations') ? $product->variations->where('is_active', true)->isNotEmpty() : false;
            @endphp
            <div class="{{ $productColClass ?? 'col-6 col-md-4 col-lg-3' }}">
                <div class="card pos-product-card h-100 {{ $outOfStock ? 'out-of-stock' : '' }}"
                     data-product-id="{{ $product->id }}"
                     data-product-name="{{ $product->name }}"
                     data-product-price="{{ $product->price }}"
                     @if($hasVariations) data-has-variations="1" @endif>
                    <div class="position-relative">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted" style="height: 120px;">
                                <i class="ti ti-package fs-1"></i>
                            </div>
                        @endif
                        <span class="badge {{ $stockClass }} pos-badge-stock position-absolute top-0 end-0 m-1">{{ $availableQty }}</span>
                        @if($hasVariations)
                            <span class="badge bg-info position-absolute top-0 start-0 m-1" style="font-size:.6rem;">
                                <i class="ti ti-layers-subtract"></i> {{ $product->variations->where('is_active', true)->count() }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-2 d-flex flex-column">
                        <div class="small text-muted text-truncate">{{ $product->category->name ?? '' }}</div>
                        <div class="fw-semibold text-truncate mb-1" title="{{ $product->name }}">{{ $product->name }}</div>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="fw-bold text-primary">{{ number_format($product->price, 0, ',', ' ') }}</span>
                            <span class="btn btn-sm btn-primary rounded-circle pos-add-btn" {{ $outOfStock ? 'disabled' : '' }} title="{{ __('Ajouter') }}">
                                <i class="ti ti-plus"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="ti ti-search-off fs-1 d-block mb-2"></i>
                    {{ __('Aucun produit trouve.') }}
                </div>
            </div>
        @endforelse
    </div>
</div>

@if($products->hasPages())
    <div class="mt-3 pos-pagination">{{ $products->appends(request()->query())->links() }}</div>
@endif

{{-- Variation Selection Modal --}}
<div class="modal fade" id="variationModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="variationModalTitle">{{ __('Choisir une variante') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="variationModalBody">
                {{-- Populated dynamically --}}
            </div>
        </div>
    </div>
</div>
