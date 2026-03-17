@extends('eshop360::channel-shop.layout')

@section('content')
<nav aria-label="{{ __('breadcrumb') }}" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">{{ __('Catalog') }}</a>
        </li>
        @if($product->category)
            <li class="breadcrumb-item">{{ $product->category->name }}</li>
        @endif
        <li class="breadcrumb-item active">{{ $product->name }}</li>
    </ol>
</nav>

<div class="row g-4">
    <div class="col-md-5">
        @if($product->image)
            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid rounded shadow-sm w-100" style="object-fit: cover; max-height: 450px;">
        @else
            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                <span class="text-muted fs-5">{{ __('No image') }}</span>
            </div>
        @endif
    </div>

    <div class="col-md-7">
        <h2 class="fw-bold mb-2">{{ $product->name }}</h2>

        <div class="text-muted mb-3">
            <code>{{ $product->sku }}</code>
            @if($product->barcode)
                &middot; {{ $product->barcode }}
            @endif
        </div>

        <div class="mb-3">
            @if($product->category)
                <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
            @endif
            @if($product->brand)
                <span class="badge bg-light text-dark border">{{ $product->brand->name }}</span>
            @endif
        </div>

        <div class="mb-4">
            <span class="fs-2 fw-bold text-primary">{{ number_format((float) $product->display_price, 2) }}</span>
            @if((float) $product->display_original_price > (float) $product->display_price)
                <span class="text-muted text-decoration-line-through fs-5 ms-2">{{ number_format((float) $product->display_original_price, 2) }}</span>
                @php
                    $saving = round(((float) $product->display_original_price - (float) $product->display_price) / (float) $product->display_original_price * 100);
                @endphp
                <span class="badge bg-success ms-2">-{{ $saving }}%</span>
            @endif
        </div>

        <div class="mb-4">
            @if($product->display_stock > 0)
                <span class="text-success fw-semibold">{{ $product->display_stock }} {{ __('in stock') }}</span>
            @else
                <span class="text-danger fw-semibold">{{ __('Out of stock') }}</span>
            @endif
        </div>

        @if($product->description)
            <div class="mb-4">
                <h6 class="fw-semibold">{{ __('Description') }}</h6>
                <p class="text-muted">{{ $product->description }}</p>
            </div>
        @endif

        @auth
            <form method="POST" action="{{ route('eshop360.channel-shop.cart.add', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="d-flex gap-3 align-items-end">
                    <div>
                        <label class="form-label fw-semibold" for="product-qty">{{ __('Quantity') }}</label>
                        <input id="product-qty" type="number" name="quantity" value="1" min="1" class="form-control" style="width: 100px;">
                    </div>
                    <button type="submit" class="btn btn-shop btn-lg px-5" {{ $product->display_stock <= 0 ? 'disabled' : '' }}>
                        {{ __('Add to Cart') }}
                    </button>
                </div>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg">{{ __('Login to order') }}</a>
        @endauth
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}" class="btn btn-outline-secondary">
        &larr; {{ __('Back to catalog') }}
    </a>
</div>
@endsection
