<x-dashboard::layouts.master
    :title="'Low Stocks - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Low Stocks">

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">Low Stocks</h4>
        <h6>Products at or below their alert threshold</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.stocks.index', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>All stock
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('eshop360.stocks.low', $instance->slug ?? '') }}" class="row g-3">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input
                    id="search"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Product name">
            </div>
            <div class="col-md-4">
                <label for="warehouse_id" class="form-label">Warehouse</label>
                <select id="warehouse_id" name="warehouse_id" class="form-select">
                    <option value="">All warehouses</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected((string) request('warehouse_id') === (string) $warehouse->id)>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('eshop360.stocks.low', $instance->slug ?? '') }}" class="btn btn-light w-100">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Warehouse stock</th>
                        <th>Alert quantity</th>
                        <th>Expiry date</th>
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
                            <td>
                                @php
                                    $stockLines = $product->stocks
                                        ->when(request('warehouse_id'), fn ($collection) => $collection->where('warehouse_id', (int) request('warehouse_id')));
                                @endphp
                                <div class="fw-semibold">{{ $stockLines->sum('quantity') }}</div>
                                @foreach($stockLines as $stock)
                                    <div class="text-muted small">
                                        {{ $stock->warehouse->name ?? 'Warehouse' }}: {{ $stock->quantity }}
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ $product->alert_quantity }}</td>
                            <td>{{ optional($product->expiry_date)->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No low stock products found.</td>
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
