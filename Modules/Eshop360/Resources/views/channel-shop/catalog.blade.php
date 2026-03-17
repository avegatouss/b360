@extends('eshop360::channel-shop.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ __('Catalog') }}</h2>
        <p class="text-muted mb-0">{{ __('Browse products available in') }} {{ $channel->name }}</p>
    </div>
    @auth
        <a href="{{ route('eshop360.channel-shop.cart', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-shop">
            {{ __('Cart') }} ({{ count($cart) }})
        </a>
    @endauth
</div>

<div class="row g-4">
    {{-- Sidebar Filters --}}
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header fw-semibold">{{ __('Filters') }}</div>
            <div class="card-body">
                <form method="GET" action="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
                    <div class="mb-3">
                        <label class="form-label" for="shop-search">{{ __('Search') }}</label>
                        <input id="shop-search" type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('Product name, SKU...') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="shop-category">{{ __('Category') }}</label>
                        <select id="shop-category" name="category_id" class="form-select">
                            <option value="">{{ __('All categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-shop w-100">{{ __('Filter') }}</button>
                    <a href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-light w-100 mt-2">{{ __('Reset') }}</a>
                </form>
            </div>
        </div>

        {{-- Cart Snapshot --}}
        @auth
        @if(count($cart) > 0)
            <div class="card mt-3">
                <div class="card-header fw-semibold d-flex justify-content-between">
                    <span>{{ __('Cart') }}</span>
                    <span class="badge bg-primary rounded-pill">{{ count($cart) }}</span>
                </div>
                <div class="card-body p-2">
                    @foreach($cart as $item)
                        <div class="d-flex justify-content-between py-1 px-1 border-bottom small">
                            <span>{{ \Illuminate\Support\Str::limit($item['name'], 20) }}</span>
                            <span class="fw-semibold">{{ $item['quantity'] }} x {{ number_format((float) $item['unit_price'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer text-end">
                    <strong>{{ number_format((float) $totals['total'], 2) }}</strong>
                </div>
            </div>
        @endif
        @endauth
    </div>

    {{-- Product Grid --}}
    <div class="col-lg-9">
        <div class="row g-3">
            @forelse($products as $product)
                <div class="col-md-6 col-xl-4">
                    <div class="card product-card h-100">
                        @if($product->image)
                            <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <span class="text-muted">{{ __('No image') }}</span>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-semibold mb-1">
                                <a href="{{ route('eshop360.channel-shop.product', [$instance->slug ?? '', $channel->slug ?? $channel->id, $product->id]) }}" class="text-decoration-none text-dark">
                                    {{ $product->name }}
                                </a>
                            </h6>
                            <div class="text-muted small mb-2">
                                {{ $product->category->name ?? '' }}
                                @if($product->brand) &middot; {{ $product->brand->name }} @endif
                            </div>
                            <div class="mb-2">
                                <span class="fs-5 fw-bold text-primary">{{ number_format((float) $product->display_price, 2) }}</span>
                                @if((float) $product->display_original_price > (float) $product->display_price)
                                    <span class="text-muted text-decoration-line-through small ms-1">{{ number_format((float) $product->display_original_price, 2) }}</span>
                                @endif
                            </div>
                            <div class="small text-muted mb-3">
                                {{ $product->display_stock }} {{ __('in stock') }}
                            </div>
                            <div class="mt-auto">
                                @auth
                                    <form method="POST" action="{{ route('eshop360.channel-shop.cart.add', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <div class="input-group">
                                            <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width: 70px;">
                                            <button type="submit" class="btn btn-shop flex-fill" {{ $product->display_stock <= 0 ? 'disabled' : '' }}>
                                                {{ __('Add to Cart') }}
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">{{ __('Login to order') }}</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5">
                        <h5>{{ __('No products found') }}</h5>
                        <p>{{ __('Try adjusting your filters or check back later.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if($products->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
