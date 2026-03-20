@php $slug = $instance->slug ?? ''; @endphp

<x-dashboard::layouts.master
    :title="__('Mon panier') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Mon panier')">

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold"><i class="ti ti-shopping-cart me-2"></i>{{ __('Mon panier') }}</h4>
        <h6>{{ $customer->name }} <span class="text-muted">&middot; {{ count($cart) }} {{ __('article(s)') }}</span></h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.portal.catalog', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Catalogue') }}</a>
        <a href="{{ route('eshop360.portal.orders.index', $slug) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-list me-1"></i>{{ __('Commandes') }}</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Produit') }}</th>
                                <th class="text-end">{{ __('Prix unit.') }}</th>
                                <th class="text-center" style="width:220px;">{{ __('Quantite') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cart as $key => $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item['name'] }}</div>
                                        <code class="text-muted" style="font-size:.8rem;">{{ $item['sku'] }}</code>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-medium">{{ number_format((float) $item['unit_price'], 0, ',', ' ') }}</div>
                                        @if((float) ($item['original_price'] ?? 0) > (float) $item['unit_price'])
                                            <div class="text-muted text-decoration-line-through" style="font-size:.85rem;">{{ number_format((float) $item['original_price'], 0, ',', ' ') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('eshop360.portal.cart.update', [$slug, $key]) }}" class="d-flex align-items-center justify-content-center gap-2">
                                            @csrf @method('PUT')
                                            <button type="submit" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}" class="btn btn-outline-secondary btn-sm px-2" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                                                <i class="ti ti-minus"></i>
                                            </button>
                                            <input type="number" name="quantity" min="1" value="{{ $item['quantity'] }}" class="form-control text-center fw-bold" style="width:70px;">
                                            <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="btn btn-outline-secondary btn-sm px-2">
                                                <i class="ti ti-plus"></i>
                                            </button>
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('Mettre a jour') }}">
                                                <i class="ti ti-refresh"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end fw-bold fs-6">{{ number_format((float) $item['total'], 0, ',', ' ') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('eshop360.portal.cart.remove', [$slug, $key]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Retirer') }}"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>
                                        {{ __('Votre panier est vide') }}
                                        <div class="mt-2"><a href="{{ route('eshop360.portal.catalog', $slug) }}" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>{{ __('Parcourir le catalogue') }}</a></div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(!empty($cart))
                <div class="card-footer d-flex justify-content-end">
                    <form method="POST" action="{{ route('eshop360.portal.cart.clear', $slug) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('{{ __('Vider tout le panier ?') }}')">
                            <i class="ti ti-trash me-1"></i>{{ __('Vider le panier') }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h5 class="mb-0 fw-bold"><i class="ti ti-receipt me-2"></i>{{ __('Passer commande') }}</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">{{ __('Sous-total') }}</span><span class="fw-medium">{{ number_format((float) $totals['subtotal'], 0, ',', ' ') }}</span></div>
                @if($totals['tax'] > 0)
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">{{ __('Taxes') }}</span><span>{{ number_format((float) $totals['tax'], 0, ',', ' ') }}</span></div>
                @endif
                <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2 mb-3"><span>{{ __('Total') }}</span><span class="text-primary">{{ number_format((float) $totals['total'], 0, ',', ' ') }}</span></div>

                <form method="POST" action="{{ route('eshop360.portal.checkout', $slug) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Adresse de livraison') }} <span class="text-danger">*</span></label>
                        <textarea name="delivery_address" rows="3" class="form-control" required placeholder="{{ __('Adresse complete...') }}">{{ old('delivery_address', $customer->address) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="{{ __('Instructions speciales...') }}">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg" {{ empty($cart) ? 'disabled' : '' }}>
                        <i class="ti ti-check me-1"></i>{{ __('Confirmer la commande') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
