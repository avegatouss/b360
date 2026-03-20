@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

{{-- Top Bar: Register & Holdings --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                @if($currentRegister)
                    <span class="badge bg-success fs-6"><i class="ti ti-cash-register me-1"></i> {{ __('Caisse ouverte') }} #{{ $currentRegister->id }}</span>
                    <form method="POST" action="{{ route('eshop360.channel-portal.pos.registers.close', [$slug, $channelKey]) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="ti ti-lock me-1"></i> {{ __('Fermer caisse') }}
                        </button>
                    </form>
                @else
                    <span class="badge bg-secondary fs-6"><i class="ti ti-lock me-1"></i> {{ __('Caisse fermee') }}</span>
                    <form method="POST" action="{{ route('eshop360.channel-portal.pos.registers.open', [$slug, $channelKey]) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-lock-open me-1"></i> {{ __('Ouvrir caisse') }}
                        </button>
                    </form>
                @endif
            </div>
            @if(!empty($holdings) && count($holdings) > 0)
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="ti ti-player-pause me-1"></i> {{ __('En attente') }} ({{ count($holdings) }})
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach($holdings as $holding)
                            <li>
                                <form method="POST" action="{{ route('eshop360.channel-portal.pos.holdings.resume', [$slug, $channelKey, $holding->id]) }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="ti ti-player-play me-1"></i>
                                        {{ $holding->customer->name ?? __('Client anonyme') }}
                                        &mdash; {{ number_format($holding->total ?? 0, 0, ',', ' ') }} XAF
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- LEFT: Products --}}
    <div class="col-lg-7 col-xl-8">
        {{-- Search/Filter Bar --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('Rechercher un produit...') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select form-select-sm">
                            <option value="">{{ __('Categorie') }}</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="brand" class="form-select form-select-sm">
                            <option value="">{{ __('Marque') }}</option>
                            @foreach($brands ?? [] as $brand)
                                <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="ti ti-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="row g-2">
            @forelse($products as $product)
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-2 text-center">
                            <h6 class="fw-bold mb-1 small text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                            <p class="text-muted mb-1" style="font-size:.75rem;"><code>{{ $product->sku ?? '---' }}</code></p>
                            <p class="fw-bold text-primary mb-2">{{ number_format($product->pivot->sale_price ?? $product->price ?? 0, 0, ',', ' ') }} XAF</p>
                            <form method="POST" action="{{ route('eshop360.channel-portal.cart.add', [$slug, $channelKey]) }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="price" value="{{ $product->pivot->sale_price ?? $product->price ?? 0 }}">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="ti ti-plus me-1"></i> {{ __('Ajouter') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5">{{ __('Aucun produit trouve.') }}</div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>

    {{-- RIGHT: Cart --}}
    <div class="col-lg-5 col-xl-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 75px;">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="ti ti-shopping-cart me-1"></i> {{ __('Panier') }}</h6>
                @if(!empty($cartItems) && count($cartItems) > 0)
                    <form method="POST" action="{{ route('eshop360.channel-portal.cart.clear', [$slug, $channelKey]) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="ti ti-trash me-1"></i> {{ __('Vider') }}
                        </button>
                    </form>
                @endif
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                @forelse($cartItems ?? [] as $index => $item)
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-medium small">{{ $item['name'] ?? '---' }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ number_format($item['price'] ?? 0, 0, ',', ' ') }} XAF</div>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <form method="POST" action="{{ route('eshop360.channel-portal.cart.update', [$slug, $channelKey]) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="index" value="{{ $index }}">
                                <input type="number" name="quantity" value="{{ $item['quantity'] ?? 1 }}" min="1" class="form-control form-control-sm text-center" style="width:60px;" onchange="this.form.submit()">
                            </form>
                            <form method="POST" action="{{ route('eshop360.channel-portal.cart.remove', [$slug, $channelKey]) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="index" value="{{ $index }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-x"></i></button>
                            </form>
                        </div>
                        <div class="fw-bold small ms-2" style="min-width: 80px; text-align: right;">
                            {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', ' ') }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">{{ __('Le panier est vide.') }}</div>
                @endforelse
            </div>
            @php
                $cartTotal = collect($cartItems ?? [])->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 1));
            @endphp
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between fw-bold mb-3">
                    <span>{{ __('Total') }}</span>
                    <span class="text-primary fs-5">{{ number_format($cartTotal, 0, ',', ' ') }} XAF</span>
                </div>
                <div class="d-flex gap-2">
                    @if(!empty($cartItems) && count($cartItems) > 0)
                        <form method="POST" action="{{ route('eshop360.channel-portal.pos.holdings.store', [$slug, $channelKey]) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="ti ti-player-pause me-1"></i> {{ __('Pause') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('eshop360.channel-portal.pos.checkout', [$slug, $channelKey]) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ti ti-check me-1"></i> {{ __('Encaisser') }}
                            </button>
                        </form>
                    @else
                        <button class="btn btn-outline-warning w-100 flex-fill" disabled>
                            <i class="ti ti-player-pause me-1"></i> {{ __('Pause') }}
                        </button>
                        <button class="btn btn-success w-100 flex-fill" disabled>
                            <i class="ti ti-check me-1"></i> {{ __('Encaisser') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
