<x-dashboard::layouts.master
    :title="__('Expired Products') . ' -' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Expired Products')">

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('Expired Products') }}</h4>
        <h6>{{ __('Products with an expiry date before today') }}</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.stocks.expiry-report', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-report me-1"></i>Expiry report
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('eshop360.stocks.expired', $instance->slug ?? '') }}" class="row g-3">
            <div class="col-md-9">
                <label for="search" class="form-label">{{ __('Search') }}</label>
                <input
                    id="search"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="{{ __('Product name') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                <a href="{{ route('eshop360.stocks.expired', $instance->slug ?? '') }}" class="btn btn-light w-100">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Manufactured date') }}</th>
                        <th>{{ __('Expiry date') }}</th>
                        <th>{{ __('Warehouses') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                @if($product->category)
                                    <small class="text-muted">{{ $product->category->name }}</small>
                                @endif
                            </td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td>{{ optional($product->manufactured_date)->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ optional($product->expiry_date)->format('Y-m-d') ?? '-' }}</td>
                            <td>
                                @forelse($product->stocks as $stock)
                                    <div class="text-muted small">
                                        {{ $stock->warehouse->name ?? 'Warehouse' }}: {{ $stock->quantity }}
                                    </div>
                                @empty
                                    <span class="text-muted">{{ __('No stock line') }}</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">{{ __('No expired products found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
